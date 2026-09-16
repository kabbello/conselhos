<?php

namespace Modules\Conselhos\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Comissoes\Models\Comissao;
use Modules\Composicao\Models\Composicao;
use Modules\Documentos\Models\Documento;
use Modules\Documentos\Models\Legislacao;
use Modules\Municipios\Models\Municipio;
use Modules\Resolucoes\Models\AtoNormativo;
use Modules\Resolucoes\Models\Processo;
use Modules\Reunioes\Models\Reuniao;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Conselho extends Model
{
    use HasFactory, LogsActivity;

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
        'notif_email_ativo',
        'notif_whatsapp_ativo',
    ];

    protected $casts = [
        'ativo'                => 'boolean',
        'notif_email_ativo'    => 'boolean',
        'notif_whatsapp_ativo' => 'boolean',
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

    public function comissoes(): HasMany
    {
        return $this->hasMany(Comissao::class);
    }

    public function processos(): HasMany
    {
        return $this->hasMany(Processo::class);
    }

    public function atosNormativos(): HasMany
    {
        return $this->hasMany(AtoNormativo::class);
    }

    public function reunioes(): HasMany
    {
        return $this->hasMany(Reuniao::class);
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }

    public function legislacoes(): HasMany
    {
        return $this->hasMany(Legislacao::class);
    }

    /**
     * Usuários com papel gestor_conselho atribuído a este conselho.
     * Apenas vínculos ativos (revogado_em nulo) são retornados.
     */
    public function gestoresUsuarios(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_conselho_gestores',
            'conselho_id',
            'user_id',
        )
        ->withPivot(['atribuido_por', 'atribuido_em', 'revogado_em'])
        ->wherePivotNull('revogado_em');
    }
}
