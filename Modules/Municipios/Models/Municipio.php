<?php

namespace Modules\Municipios\Models;

use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Modules\Conselhos\Models\Conselho;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Municipio extends Model implements HasName
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'nome',
        'slug',
        'sigla',
        'uf',
        'codigo_ibge',
        'logo_path',
        'brasao_path',
        'email',
        'telefone',
        'site',
        'endereco',
        'cep',
        'prefeito',
        'populacao',
        'area_km2',
        'cor_primaria',
        'descricao',
        'ativo',
    ];

    protected $casts = [
        'ativo'      => 'boolean',
        'populacao'  => 'integer',
        'area_km2'   => 'decimal:2',
    ];

    public function getFilamentName(): string
    {
        return $this->nome;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->dontLogIfAttributesChangedOnly(['updated_at']);
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null;
    }

    public function getBrasaoUrlAttribute(): ?string
    {
        return $this->brasao_path ? Storage::disk('public')->url($this->brasao_path) : null;
    }

    public function conselhos(): HasMany
    {
        return $this->hasMany(Conselho::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(\App\Models\User::class);
    }
}
