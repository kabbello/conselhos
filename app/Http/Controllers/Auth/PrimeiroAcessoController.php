<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Modules\Composicao\Models\Conselheiro;

/**
 * Fluxo de primeiro acesso para conselheiros.
 *
 * Regra de segurança: o e-mail deve ter sido previamente cadastrado pelo
 * administrador municipal no registro do conselheiro. O próprio conselheiro
 * NÃO pode associar ou alterar o e-mail por esta rota — isso eliminaria
 * a garantia de identidade e abriria brecha de acesso não autorizado.
 */
class PrimeiroAcessoController extends Controller
{
    public function show()
    {
        return view('auth.primeiro-acesso');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ], [
            'email.required' => 'Informe o e-mail cadastrado pelo administrador.',
            'email.email'    => 'Informe um e-mail válido.',
        ]);

        $email = mb_strtolower(trim($request->email));

        // Busca apenas pelo e-mail já registrado pelo admin — sem busca por CPF
        $conselheiro = Conselheiro::where('ativo', true)
            ->where('email', $email)
            ->whereHas('composicoesAtivas')
            ->first();

        // Resposta genérica intencional: não revela se o e-mail existe ou não
        // (previne enumeração de conselheiros cadastrados)
        if (! $conselheiro) {
            return back()->with(
                'sucesso',
                'Se o e-mail informado estiver cadastrado para um conselheiro ativo, você receberá o link de acesso em instantes.'
            );
        }

        $this->criarContaEEnviarLink($conselheiro);

        return back()->with(
            'sucesso',
            'Se o e-mail informado estiver cadastrado para um conselheiro ativo, você receberá o link de acesso em instantes.'
        );
    }

    private function criarContaEEnviarLink(Conselheiro $conselheiro): void
    {
        $user = $conselheiro->user;

        if (! $user) {
            $user = User::create([
                'municipio_id'        => $conselheiro->municipio_id,
                'name'                => $conselheiro->nome,
                'email'               => $conselheiro->email,
                'password'            => Hash::make(Str::random(32)),
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

        if (! $user->hasRole('conselheiro')) {
            $user->assignRole('conselheiro');
        }

        Password::sendResetLink(['email' => $user->email]);

        activity('primeiro-acesso')
            ->causedBy($user)
            ->performedOn($conselheiro)
            ->withProperties(['ip' => request()->ip()])
            ->event('link_enviado')
            ->log("Link de acesso enviado para {$user->email}");
    }
}
