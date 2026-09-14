<?php

namespace Modules\Documentos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Conselhos\Models\Conselho;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Base legal do conselho: leis, decretos, portarias que fundamentam sua criação e funcionamento.
 *
 * Diferente dos atos normativos (que o conselho PRODUZ), a legislação é o arcabouço
 * legal EXTERNO que regula o conselho (ex.: Lei 8.142/90, Decreto Municipal de criação).
 */
class Legislacao extends Model
{
    use LogsActivity, SoftDeletes;

    protected $table = 'legislacao';

    protected $fillable = [
        'conselho_id',
        'tipo_legislacao_id',
        'created_by',
        'titulo',
        'descricao',
        'numero',
        'data',
        'hash_integridade',
        'legacy_id',
    ];

    protected $casts = [
        'data' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['titulo', 'numero', 'conselho_id']);
    }

    public function conselho(): BelongsTo
    {
        return $this->belongsTo(Conselho::class);
    }

    public function tipoLegislacao(): BelongsTo
    {
        return $this->belongsTo(TipoLegislacao::class);
    }

    /**
     * Rótulo formatado: "Lei nº 8.142/1990"
     */
    public function getLabelAttribute(): string
    {
        $tipo = $this->tipoLegislacao?->nome ?? 'Documento';

        return trim("{$tipo} nº {$this->numero}");
    }
}
