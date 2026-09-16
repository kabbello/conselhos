<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Envia mensagens de texto via Evolution API (WhatsApp).
 *
 * Configuração (env):
 *   WHATSAPP_API_URL   = https://api.evolution.example.com
 *   WHATSAPP_API_KEY   = your-api-key
 *   WHATSAPP_INSTANCE  = instance-name
 *
 * A mesma instância é compartilhada por toda a plataforma. A ativação
 * por conselho é controlada pelo campo notif_whatsapp_ativo no Conselho.
 */
class EvolutionApiService
{
    public function enabled(): bool
    {
        return filled(config('services.evolution.url'))
            && filled(config('services.evolution.key'))
            && filled(config('services.evolution.instance'));
    }

    /**
     * Envia texto para um número de telefone.
     *
     * @param  string  $numero  Qualquer formato; será normalizado para dígitos + DDI 55
     * @return bool  true se a API retornou 2xx
     */
    public function sendText(string $numero, string $texto): bool
    {
        if (! $this->enabled()) {
            return false;
        }

        $numero = $this->normalizar($numero);
        if (! $numero) {
            return false;
        }

        try {
            $response = Http::withHeaders(['apikey' => config('services.evolution.key')])
                ->timeout(15)
                ->post(
                    rtrim(config('services.evolution.url'), '/') . '/message/sendText/' . config('services.evolution.instance'),
                    [
                        'number' => $numero,
                        'text'   => $texto,
                    ]
                );

            if (! $response->successful()) {
                Log::warning('EvolutionApi: falha no envio', [
                    'numero' => $numero,
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
            }

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('EvolutionApi: exceção no envio', [
                'numero'    => $numero,
                'exception' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Normaliza número: remove não-dígitos, adiciona DDI 55 se necessário.
     */
    private function normalizar(string $numero): string
    {
        $numero = preg_replace('/\D/', '', $numero);

        if (strlen($numero) === 10 || strlen($numero) === 11) {
            $numero = '55' . $numero;
        }

        // Deve ter entre 12 e 13 dígitos (DDI + DDD + número)
        if (strlen($numero) < 12 || strlen($numero) > 13) {
            return '';
        }

        return $numero;
    }
}
