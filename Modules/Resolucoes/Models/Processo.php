<?php

namespace Modules\Resolucoes\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Comissoes\Models\Comissao;
use Modules\Conselhos\Models\Conselho;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Processo extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'conselho_id',
        'numero',
        'ano',
        'titulo',
        'descricao',
        'tipo',
        'status',
        'origem',
        'requerente',
        'comissao_id',
        'data_abertura',
        'data_encerramento',
        'observacoes',
        'legacy_id',
    ];

    protected $casts = [
        'ano'               => 'integer',
        'data_abertura'     => 'date',
        'data_encerramento' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['numero', 'ano', 'titulo', 'status', 'tipo', 'conselho_id']);
    }

    // ---------- Helpers ----------

    public function getNumeroCompletoAttribute(): string
    {
        if (! $this->numero) {
            return "/{$this->ano}";
        }

        return str_pad($this->numero, 3, '0', STR_PAD_LEFT) . "/{$this->ano}";
    }

    public function isEncerrado(): bool
    {
        return in_array($this->status, ['APROVADO', 'REJEITADO', 'ARQUIVADO']);
    }

    // ---------- Relacionamentos ----------

    public function conselho(): BelongsTo
    {
        return $this->belongsTo(Conselho::class);
    }

    public function comissao(): BelongsTo
    {
        return $this->belongsTo(Comissao::class);
    }

    public function atosNormativos(): HasMany
    {
        return $this->hasMany(AtoNormativo::class);
    }
}
