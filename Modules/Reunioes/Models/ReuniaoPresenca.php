<?php

namespace Modules\Reunioes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Composicao\Models\Composicao;

class ReuniaoPresenca extends Model
{
    protected $table = 'reuniao_presencas';

    protected $fillable = [
        'reuniao_id',
        'composicao_id',
        'presente',
    ];

    protected $casts = [
        'presente' => 'boolean',
    ];

    public function reuniao(): BelongsTo
    {
        return $this->belongsTo(Reuniao::class);
    }

    public function composicao(): BelongsTo
    {
        return $this->belongsTo(Composicao::class);
    }
}
