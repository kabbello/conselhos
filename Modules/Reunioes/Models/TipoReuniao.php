<?php

namespace Modules\Reunioes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Municipios\Models\Municipio;

class TipoReuniao extends Model
{
    protected $table = 'tipos_reuniao';

    protected $fillable = [
        'municipio_id',
        'nome',
    ];

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    public function reunioes(): HasMany
    {
        return $this->hasMany(Reuniao::class, 'tipo_id');
    }
}
