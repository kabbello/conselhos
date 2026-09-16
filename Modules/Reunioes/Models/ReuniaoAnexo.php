<?php

namespace Modules\Reunioes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ReuniaoAnexo extends Model
{
    use SoftDeletes;

    protected $table = 'reuniao_anexos';

    protected $fillable = [
        'reuniao_id',
        'titulo',
        'tipo',
        'descricao',
        'arquivo_path',
        'publicado',
        'data_documento',
    ];

    protected $casts = [
        'publicado'      => 'boolean',
        'data_documento' => 'date',
    ];

    // ---------- Acessores ----------

    public function getArquivoUrlAttribute(): ?string
    {
        return $this->arquivo_path
            ? Storage::disk('r2')->url($this->arquivo_path)
            : null;
    }

    // ---------- Relacionamentos ----------

    public function reuniao(): BelongsTo
    {
        return $this->belongsTo(Reuniao::class);
    }
}
