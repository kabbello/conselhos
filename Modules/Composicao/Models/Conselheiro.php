<?php

namespace Modules\Composicao\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Modules\Municipios\Models\Municipio;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Conselheiro extends Model
{
    use HasFactory, LogsActivity, Notifiable, SoftDeletes;

    protected $fillable = [
        'municipio_id',
        'user_id',
        'legacy_id',
        'nome',
        'email',
        'telefone',
        'cpf',
        'foto_path',
        'ativo',
    ];

    protected $hidden = [
        'cpf',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['nome', 'email', 'telefone', 'ativo', 'municipio_id']);
    }

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function composicoes(): HasMany
    {
        return $this->hasMany(Composicao::class);
    }

    public function composicoesAtivas(): HasMany
    {
        return $this->hasMany(Composicao::class)->where('ativo', true)->whereNull('deleted_at');
    }
}
