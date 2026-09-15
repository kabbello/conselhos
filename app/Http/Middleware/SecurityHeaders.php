<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A06: adiciona headers de segurança HTTP em todas as respostas.
 * Referência: OWASP Secure Headers Project.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Impede clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Impede MIME sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Controla informações enviadas no Referer
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Desativa features desnecessárias do browser
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        // HSTS: força HTTPS por 1 ano (apenas em produção para não quebrar dev HTTP)
        if (app()->isProduction()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // CSP: política restritiva para Filament/Livewire/Alpine.
        //
        // 'unsafe-inline' em script-src e style-src: necessário porque Livewire injeta
        // scripts inline e Alpine.js usa atributos x-data/x-on diretamente no HTML.
        // Mitigação ideal futura: implementar nonce por request e passar via
        // Filament::addScriptData() — requer versão do Filament com suporte a CSP nonce.
        //
        // 'unsafe-eval' foi REMOVIDO: Alpine.js v3 compilado via Vite não requer eval.
        // Se algum plugin ou widget de terceiro quebrar, investigar antes de reativar.
        $r2Url = rtrim(config('filesystems.disks.r2.url', ''), '/');
        $r2Host = $r2Url ? parse_url($r2Url, PHP_URL_HOST) : null;
        $imgSrc = $r2Host ? "img-src 'self' data: blob: https://{$r2Host}" : "img-src 'self' data: blob:";

        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline'",  // Livewire/Alpine: inline necessário; eval removido
            "style-src 'self' 'unsafe-inline'",   // Tailwind inline styles
            $imgSrc,
            "font-src 'self' data: https://fonts.bunny.net",
            "connect-src 'self'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ]));

        // Remove header que expõe versão do servidor
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}
