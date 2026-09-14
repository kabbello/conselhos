<?php

namespace Modules\Comissoes\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Conselhos\Models\Conselho;
use Modules\Resolucoes\Models\Processo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Comissao extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'conselho_id',
        'nome',
        'tipo',
        'finalidade',
        'ato_criacao',
        'data_criacao',
        'data_encerramento',
        'status',
        'ativo',
        'observacoes',
        'legacy_id',
    ];

    protected $casts = [
        'data_criacao'      => 'date',
        'data_encerramento' => 'date',
        'ativo'             => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['nome', 'tipo', 'status', 'ativo', 'conselho_id']);
    }

    // ---------- Helpers ----------

    public function isAtiva(): bool
    {
        return $this->status === 'ATIVA' && $this->ativo;
    }

    // ---------- Relacionamentos ----------

    public function conselho(): BelongsTo
    {
        return $this->belongsTo(Conselho::class);
    }

    public function membros(): HasMany
    {
        return $this->hasMany(ComissaoMembro::class);
    }

    public function membrosAtivos(): HasMany
    {
        return $this->hasMany(ComissaoMembro::class)->where('ativo', true)->whereNull('deleted_at');
    }

    public function reunioes(): HasMany
    {
        return $this->hasMany(ComissaoReuniao::class);
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(ComissaoDocumento::class);
    }

    public function processos(): HasMany
    {
        return $this->hasMany(Processo::class);
    }
}
