<?php

namespace Modules\Reunioes\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Conselhos\Models\Conselho;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Reuniao extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'reunioes';

    protected $fillable = [
        'conselho_id',
        'numero',
        'tipo_id',
        'created_by',
        'data_hora',
        'local',
        'pauta',
        'ata_texto',
        'ata_aprovada',
        'ata_aprovada_em',
        'observacoes',
        'status',
        'legacy_id',
    ];

    protected $casts = [
        'data_hora'      => 'datetime',
        'ata_aprovada'   => 'boolean',
        'ata_aprovada_em' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['status', 'data_hora', 'conselho_id']);
    }

    // ---------- Helpers ----------

    public function isRealizada(): bool
    {
        return $this->status === 'realizada';
    }

    public function isAgendada(): bool
    {
        return $this->status === 'agendada';
    }

    // ---------- Relacionamentos ----------

    public function conselho(): BelongsTo
    {
        return $this->belongsTo(Conselho::class);
    }

    public function tipoReuniao(): BelongsTo
    {
        return $this->belongsTo(TipoReuniao::class, 'tipo_id');
    }

    public function presencas(): HasMany
    {
        return $this->hasMany(ReuniaoPresenca::class);
    }

    public function links(): HasMany
    {
        return $this->hasMany(ReuniaoLink::class);
    }

    public function notificacoes(): HasMany
    {
        return $this->hasMany(NotificacaoEnviada::class);
    }

    public function anexos(): HasMany
    {
        return $this->hasMany(ReuniaoAnexo::class);
    }
}
