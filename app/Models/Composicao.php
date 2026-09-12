<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
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
        'tipo_representacao',
        'data_inicio',
        'data_fim',
        'ativo',
        'observacoes',
        'legacy_id',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim'    => 'date',
        'ativo'       => 'boolean',
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

    public function entidade(): BelongsTo
    {
        return $this->belongsTo(Entidade::class);
    }

    public function cargo(): BelongsTo
    {
        return $this->belongsTo(Cargo::class);
    }

    public function isGestor(): bool
    {
        return in_array($this->tipo, ['PRESIDENTE', 'SECRETARIO']) && $this->ativo;
    }
}
