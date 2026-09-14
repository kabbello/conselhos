<?php

namespace Modules\Comissoes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Composicao\Models\Composicao;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ComissaoMembro extends Model
{
    use LogsActivity, SoftDeletes;

    protected $table = 'comissao_membros';

    protected $fillable = [
        'comissao_id',
        'composicao_id',
        'nome_exibicao',
        'papel',
        'data_entrada',
        'data_saida',
        'ativo',
        'observacoes',
    ];

    protected $casts = [
        'data_entrada' => 'date',
        'data_saida'   => 'date',
        'ativo'        => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['papel', 'ativo', 'comissao_id', 'composicao_id']);
    }

    public function comissao(): BelongsTo
    {
        return $this->belongsTo(Comissao::class);
    }

    public function composicao(): BelongsTo
    {
        return $this->belongsTo(Composicao::class);
    }

    /**
     * Nome para exibição: prioriza o campo manual; fallback para o conselheiro vinculado.
     */
    public function getNomeAttribute(): string
    {
        return $this->nome_exibicao
            ?? $this->composicao?->nome_exibicao
            ?? '(sem nome)';
    }
}
