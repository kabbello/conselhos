<?php

namespace Modules\Municipios\Models;

use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'logo_path',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function getFilamentName(): string
    {
        return $this->nome;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
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
