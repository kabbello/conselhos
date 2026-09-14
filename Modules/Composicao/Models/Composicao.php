<?php

namespace Modules\Composicao\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Conselhos\Models\Conselho;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Composicao extends Model
{
    use LogsActivity, SoftDeletes;

    protected $table = 'composicao';

    protected $fillable = [
        'conselho_id',
        'conselheiro_id',
        'entidade_id',
        'cargo_id',
        'tipo',
        'nome_exibicao',
        'email_exibicao',
        'telefone_contato',
        'data_nomeacao',
        'data_fim',
        'decreto_nomeacao',
        'ativo',
        'observacoes',
        'legacy_id',
    ];

    protected $casts = [
        'data_nomeacao' => 'date',
        'data_fim'      => 'date',
        'ativo'         => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    public function conselho(): BelongsTo
    {
        return $this->belongsTo(Conselho::class);
    }

    public function conselheiro(): BelongsTo
    {
        return $this->belongsTo(Conselheiro::class);
    }

    public function isGestor(): bool
    {
        return in_array($this->tipo, ['PRESIDENTE', 'SECRETARIO']) && $this->ativo;
    }
}
