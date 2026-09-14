<?php

namespace Modules\Reunioes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificacaoEnviada extends Model
{
    protected $table = 'notificacoes_enviadas';

    protected $fillable = [
        'reuniao_id',
        'tipo',
        'canal',
        'destino',
        'sucesso',
        'resposta',
        'enviado_em',
    ];

    protected $casts = [
        'sucesso'    => 'boolean',
        'enviado_em' => 'datetime',
    ];

    public function reuniao(): BelongsTo
    {
        return $this->belongsTo(Reuniao::class);
    }
}
