<?php

namespace Modules\Comissoes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ComissaoReuniao extends Model
{
    use LogsActivity, SoftDeletes;

    protected $table = 'comissao_reunioes';

    protected $fillable = [
        'comissao_id',
        'numero',
        'tipo',
        'data_hora',
        'local',
        'pauta',
        'status',
        'ata_texto',
        'ata_aprovada',
        'data_aprovacao_ata',
        'data_publicacao_ata',
        'observacoes',
        'legacy_id',
    ];

    protected $casts = [
        'data_hora'           => 'datetime',
        'data_aprovacao_ata'  => 'date',
        'data_publicacao_ata' => 'date',
        'ata_aprovada'        => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['numero', 'tipo', 'status', 'ata_aprovada', 'comissao_id']);
    }

    // ---------- Helpers ----------

    public function prazoPublicacaoExcedido(): bool
    {
        if ($this->status !== 'REALIZADA' || $this->data_publicacao_ata) {
            return false;
        }

        // Prazo de referência interno: 14 dias corridos após a realização.
        // O prazo real depende do regimento interno de cada conselho.
        // A LAI art. 11 trata de resposta a pedidos de acesso, não de prazo de publicação de atas.
        return $this->data_hora?->addDays(14)->isPast() ?? false;
    }

    // ---------- Relacionamentos ----------

    public function comissao(): BelongsTo
    {
        return $this->belongsTo(Comissao::class);
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(ComissaoDocumento::class, 'comissao_reuniao_id');
    }
}
