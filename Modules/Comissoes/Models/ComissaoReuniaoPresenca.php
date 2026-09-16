<?php

namespace Modules\Comissoes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComissaoReuniaoPresenca extends Model
{
    protected $table = 'comissao_reuniao_presencas';

    protected $fillable = [
        'comissao_reuniao_id',
        'comissao_membro_id',
        'presente',
    ];

    protected $casts = [
        'presente' => 'boolean',
    ];

    // ---------- Relacionamentos ----------

    public function reuniao(): BelongsTo
    {
        return $this->belongsTo(ComissaoReuniao::class, 'comissao_reuniao_id');
    }

    public function membro(): BelongsTo
    {
        return $this->belongsTo(ComissaoMembro::class, 'comissao_membro_id');
    }
}
