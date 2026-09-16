<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * C08: força a troca de senha quando must_reset_password = true.
 *
 * O flag é gravado pelo SuperAdminSeeder e por qualquer fluxo que exija
 * reset de credencial (ex.: criação de usuário pelo admin). Este middleware
 * garante que o usuário não consiga navegar pelo painel sem antes definir
 * uma nova senha.
 *
 * Rotas liberadas (sem redirect):
 *  - logout (evita sessão presa)
 *  - qualquer URL que contenha /profile (página de edição de perfil/senha)
 *  - assets e livewire (não são rotas de navegação)
 */
class ForcePasswordReset
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Durante impersonation o admin não deve ser bloqueado pelo reset de senha
        // do usuário impersonado — ele está acessando para suporte, não como o usuário real.
        if (app('impersonate')->isImpersonating()) {
            return $next($request);
        }

        if ($user && $user->must_reset_password && ! $this->isRotaPermitida($request)) {
            // Redireciona para a página de perfil do Filament onde a senha pode ser alterada.
            // O painel usa multi-tenancy; a URL exata inclui o slug do município.
            // Como fallback seguro usamos o tenant atual ou simplesmente /painel.
            $tenant = filament()->getTenant();
            $slug   = $tenant?->slug ?? '';

            $profileUrl = $slug
                ? url("/painel/municipio/{$slug}/profile")
                : url('/painel');

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Troca de senha obrigatória.'], 403);
            }

            return redirect($profileUrl)->with(
                'warning',
                'Você precisa definir uma nova senha antes de continuar.'
            );
        }

        return $next($request);
    }

    private function isRotaPermitida(Request $request): bool
    {
        $path = $request->path();

        // Página de edição de perfil (onde o usuário define a nova senha)
        if (str_contains($path, 'profile')) {
            return true;
        }

        // Rota de logout (impede sessão bloqueada permanentemente)
        if (str_contains($path, 'logout')) {
            return true;
        }

        // Requests internos do Livewire (não são navegação)
        if (str_contains($path, 'livewire')) {
            return true;
        }

        return false;
    }
}
