<?php

namespace Modules\Documentos\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Modules\Conselhos\Models\Conselho;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Documento extends Model
{
    use LogsActivity, SoftDeletes;

    protected $table = 'documentos';

    protected $fillable = [
        'conselho_id',
        'tipo_documento_id',
        'created_by',
        'titulo',
        'descricao',
        'numero_documento',
        'data_documento',
        'data_publicacao',
        'publico',
        'arquivo_url',
        'hash_integridade',
        'legacy_id',
    ];

    protected $casts = [
        'data_documento'  => 'date',
        'data_publicacao' => 'date',
        'publico'         => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['titulo', 'publico', 'conselho_id', 'hash_integridade']);
    }

    // A07: calcula SHA-256 do arquivo após salvar quando arquivo_url muda
    protected static function booted(): void
    {
        static::saved(function (Documento $doc) {
            if ($doc->wasChanged('arquivo_url') && $doc->arquivo_url) {
                $hash = self::calcularHash($doc->arquivo_url);
                if ($hash) {
                    $doc->withoutEvents(fn () => $doc->updateQuietly(['hash_integridade' => $hash]));
                }
            }
        });
    }

    private static function calcularHash(string $path): ?string
    {
        // Tenta storage local primeiro; ignora URLs externas (legado)
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return null;
        }
        if (Storage::exists($path)) {
            return hash('sha256', Storage::get($path));
        }
        return null;
    }

    /**
     * Retorna a URL pública para download do arquivo.
     *
     * Documentos do legado podem ter uma URL http(s) direta.
     * Novos documentos usam paths relativos do storage (disco privado).
     * Este accessor garante que a view sempre receba uma URL navegável.
     *
     * IMPORTANTE: só use este accessor em documentos marcados como publico=true.
     * Documentos privados devem ser entregues por rota autenticada com link temporário.
     */
    public function getUrlDownloadAttribute(): ?string
    {
        if (! $this->arquivo_url) {
            return null;
        }

        if (str_starts_with($this->arquivo_url, 'http://') || str_starts_with($this->arquivo_url, 'https://')) {
            return $this->arquivo_url;
        }

        return Storage::url($this->arquivo_url);
    }

    public function conselho(): BelongsTo
    {
        return $this->belongsTo(Conselho::class);
    }

    public function tipoDocumento(): BelongsTo
    {
        return $this->belongsTo(TipoDocumento::class);
    }
}
