<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Modules\Composicao\Models\Conselheiro;

class PrimeiroAcessoController extends Controller
{
    public function show()
    {
        return view('auth.primeiro-acesso');
    }

    public function store(Request $request)
    {
        $request->validate([
            'identificador' => ['required', 'string', 'max:255'],
        ], [
            'identificador.required' => 'Informe seu e-mail ou CPF cadastrado.',
        ]);

        $identificador = trim($request->identificador);

        // Busca o conselheiro por e-mail ou CPF (normaliza CPF)
        $cpfLimpo = preg_replace('/\D/', '', $identificador);

        $conselheiro = Conselheiro::where('ativo', true)
            ->where(function ($q) use ($identificador, $cpfLimpo) {
                $q->where('email', $identificador);
                if ($cpfLimpo) {
                    $q->orWhereRaw("REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), '/', '') = ?", [$cpfLimpo]);
                }
            })
            ->whereHas('composicoesAtivas')  // só conselheiros com mandato ativo
            ->first();

        if (! $conselheiro) {
            return back()
                ->withInput()
                ->withErrors(['identificador' => 'Nenhum conselheiro ativo encontrado com esses dados. Verifique o e-mail ou CPF e tente novamente.']);
        }

        // Precisa ter e-mail para enviar o link de acesso
        if (! $conselheiro->email) {
            // CPF encontrado mas sem e-mail — pede que informe o e-mail
            return back()
                ->withInput()
                ->with('pedir_email', true)
                ->with('conselheiro_id', $conselheiro->id)
                ->with('conselheiro_nome', $conselheiro->nome);
        }

        $this->criarContaEEnviarLink($conselheiro);

        return back()->with('sucesso', "Link de acesso enviado para {$conselheiro->email}. Verifique sua caixa de entrada (e a pasta de spam).");
    }

    public function salvarEmail(Request $request)
    {
        $request->validate([
            'conselheiro_id' => ['required', 'integer'],
            'email'          => ['required', 'email', 'max:255', 'unique:users,email', 'unique:conselheiros,email'],
        ], [
            'email.unique' => 'Este e-mail já está cadastrado no sistema.',
        ]);

        $conselheiro = Conselheiro::where('ativo', true)
            ->whereHas('composicoesAtivas')
            ->findOrFail($request->conselheiro_id);

        $conselheiro->update(['email' => $request->email]);

        activity('primeiro-acesso')
            ->performedOn($conselheiro)
            ->withProperties(['email_registrado' => $request->email, 'ip' => $request->ip()])
            ->event('email_registrado')
            ->log('Conselheiro registrou e-mail para primeiro acesso');

        $this->criarContaEEnviarLink($conselheiro->fresh());

        return redirect()->route('auth.primeiro-acesso')
            ->with('sucesso', "E-mail cadastrado! Link de acesso enviado para {$request->email}.");
    }

    private function criarContaEEnviarLink(Conselheiro $conselheiro): void
    {
        // Cria ou recupera o User vinculado ao conselheiro
        $user = $conselheiro->user;

        if (! $user) {
            $user = User::create([
                'municipio_id'        => $conselheiro->municipio_id,
                'name'                => $conselheiro->nome,
                'email'               => $conselheiro->email,
                'password'            => Hash::make(Str::random(32)), // senha temporária aleatória
                'must_reset_password' => true,
            ]);

            $conselheiro->update(['user_id' => $user->id]);

            activity('primeiro-acesso')
                ->causedBy($user)
                ->performedOn($conselheiro)
                ->withProperties(['ip' => request()->ip()])
                ->event('conta_criada')
                ->log("Conta criada para o conselheiro {$conselheiro->nome}");
        }

        // Garante o papel conselheiro
        if (! $user->hasRole('conselheiro')) {
            $user->assignRole('conselheiro');
        }

        // Envia o link de redefinição de senha pelo mecanismo padrão do Laravel
        Password::sendResetLink(['email' => $user->email]);

        activity('primeiro-acesso')
            ->causedBy($user)
            ->performedOn($conselheiro)
            ->withProperties(['ip' => request()->ip()])
            ->event('link_enviado')
            ->log("Link de acesso enviado para {$user->email}");
    }
}
