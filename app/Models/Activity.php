<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity as SpatieActivity;

/**
 * A08 — Enriquecer o activity log com IP, user agent e request ID automaticamente.
 * Basta apontar config/activitylog.php para esta classe.
 */
class Activity extends SpatieActivity
{
    protected static function booted(): void
    {
        static::creating(function (self $activity) {
            $request = request();

            if (! $request) {
                return;
            }

            $properties = $activity->properties ?? collect();

            // Adiciona apenas se ainda não foram definidos (evita sobrescrever em testes)
            if (! $properties->has('_meta')) {
                $activity->properties = $properties->put('_meta', [
                    'ip'         => $request->ip(),
                    'user_agent' => substr($request->userAgent() ?? '', 0, 200),
                    'request_id' => $request->header('X-Request-ID') ?? substr(md5(uniqid('', true)), 0, 12),
                ]);
            }
        });
    }
}
