<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Conselho extends Model
{
    use LogsActivity;

    protected $fillable = [
        'municipio_id',
        'nome',
        'slug',
        'sigla',
        'tipo',
        'descricao',
        'email',
        'telefone',
        'endereco',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    public function composicao(): HasMany
    {
        return $this->hasMany(Composicao::class);
    }

    public function membrosAtivos(): HasMany
    {
        return $this->hasMany(Composicao::class)->where('ativo', true)->whereNull('deleted_at');
    }

    public function gestores(): HasMany
    {
        return $this->hasMany(Composicao::class)
            ->whereIn('tipo', ['PRESIDENTE', 'SECRETARIO'])
            ->where('ativo', true)
            ->whereNull('deleted_at');
    }

    public function reunioes(): HasMany
    {
        return $this->hasMany(Reuniao::class);
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }
}
