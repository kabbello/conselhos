<?php

namespace Modules\Documentos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Municipios\Models\Municipio;

class TipoLegislacao extends Model
{
    protected $table = 'tipos_legislacao';

    protected $fillable = [
        'municipio_id',
        'nome',
    ];

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    public function legislacoes(): HasMany
    {
        return $this->hasMany(Legislacao::class, 'tipo_legislacao_id');
    }
}
