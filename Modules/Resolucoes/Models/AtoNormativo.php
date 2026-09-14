<?php

namespace Modules\Resolucoes\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Modules\Comissoes\Models\Comissao;
use Modules\Conselhos\Models\Conselho;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AtoNormativo extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'atos_normativos';

    protected $fillable = [
        'conselho_id',
        'processo_id',
        'comissao_id',
        'tipo',
        'numero',
        'ano',
        'numero_completo',
        'titulo',
        'ementa',
        'texto_completo',
        'status',
        'data_aprovacao',
        'data_publicacao',
        'publicado',
        'diario_oficial_referencia',
        'arquivo_path',
        'hash_integridade',
        'revoga_id',
        'revogado_por_id',
        'observacoes',
        'legacy_id',
    ];

    protected $casts = [
        'ano'             => 'integer',
        'numero'          => 'integer',
        'data_aprovacao'  => 'date',
        'data_publicacao' => 'date',
        'publicado'       => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['tipo', 'numero', 'ano', 'titulo', 'status', 'publicado', 'conselho_id']);
    }

    // ---------- Boot: auto-gera numero_completo e trata revogação ----------

    protected static function booted(): void
    {
        static::saving(function (AtoNormativo $ato) {
            if ($ato->numero && $ato->ano) {
                $ato->numero_completo = str_pad($ato->numero, 3, '0', STR_PAD_LEFT) . '/' . $ato->ano;
            }
        });

        // A07: calcula SHA-256 após salvar quando arquivo_path muda
        static::saved(function (AtoNormativo $ato) {
            if ($ato->wasChanged('arquivo_path') && $ato->arquivo_path) {
                $hash = null;
                if (! str_starts_with($ato->arquivo_path, 'http') && Storage::exists($ato->arquivo_path)) {
                    $hash = hash('sha256', Storage::get($ato->arquivo_path));
                }
                if ($hash) {
                    $ato->updateQuietly(['hash_integridade' => $hash]);
                }
            }
        });

        // Quando um ato é salvo com revoga_id, marca o ato revogado como REVOGADO.
        // Garante que o ato revogado pertence ao mesmo conselho (C07).
        // C07: não usa withoutEvents — auditoria registra a revogação explicitamente.
        static::saved(function (AtoNormativo $ato) {
            if ($ato->revoga_id && $ato->wasChanged('revoga_id')) {
                $revogado = static::where('id', $ato->revoga_id)
                    ->where('conselho_id', $ato->conselho_id)
                    ->first();

                if ($revogado) {
                    $revogado->update([
                        'status'          => 'REVOGADO',
                        'revogado_por_id' => $ato->id,
                    ]);

                    activity('atos-normativos')
                        ->performedOn($revogado)
                        ->causedBy(auth()->user())
                        ->withProperties(['revogado_por_id' => $ato->id, 'revogado_por' => $ato->numero_completo])
                        ->event('revogado')
                        ->log("Revogado por {$ato->label_completo}");
                }
            }
        });
    }

    // ---------- Helpers ----------

    public function isVigente(): bool
    {
        return $this->status === 'VIGENTE';
    }

    public function getLabelTipoAttribute(): string
    {
        return match ($this->tipo) {
            'RESOLUCAO'    => 'Resolução',
            'DELIBERACAO'  => 'Deliberação',
            'RECOMENDACAO' => 'Recomendação',
            'PARECER'      => 'Parecer',
            'MOCAO'        => 'Moção',
            'PORTARIA'     => 'Portaria',
            default        => $this->tipo,
        };
    }

    /**
     * Rótulo completo para exibição: "Resolução nº 001/2025"
     */
    public function getLabelCompletoAttribute(): string
    {
        return trim("{$this->label_tipo} nº {$this->numero_completo}");
    }

    // ---------- Relacionamentos ----------

    public function conselho(): BelongsTo
    {
        return $this->belongsTo(Conselho::class);
    }

    public function processo(): BelongsTo
    {
        return $this->belongsTo(Processo::class);
    }

    public function comissao(): BelongsTo
    {
        return $this->belongsTo(Comissao::class);
    }

    /** Ato que este ato revoga. */
    public function revoga(): BelongsTo
    {
        return $this->belongsTo(AtoNormativo::class, 'revoga_id');
    }

    /** Ato que revogou este ato. */
    public function revogadoPor(): BelongsTo
    {
        return $this->belongsTo(AtoNormativo::class, 'revogado_por_id');
    }
}
