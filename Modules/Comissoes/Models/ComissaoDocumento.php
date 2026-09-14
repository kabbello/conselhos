<?php

namespace Modules\Comissoes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ComissaoDocumento extends Model
{
    use LogsActivity, SoftDeletes;

    protected $table = 'comissao_documentos';

    protected $fillable = [
        'comissao_id',
        'comissao_reuniao_id',
        'titulo',
        'tipo',
        'descricao',
        'arquivo_path',
        'data_documento',
        'publicado',
        'data_publicacao',
        'legacy_id',
    ];

    protected $casts = [
        'data_documento' => 'date',
        'data_publicacao' => 'date',
        'publicado'       => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['titulo', 'tipo', 'publicado', 'comissao_id']);
    }

    public function comissao(): BelongsTo
    {
        return $this->belongsTo(Comissao::class);
    }

    public function reuniao(): BelongsTo
    {
        return $this->belongsTo(ComissaoReuniao::class, 'comissao_reuniao_id');
    }
}
