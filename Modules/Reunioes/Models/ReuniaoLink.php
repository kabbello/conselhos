<?php

namespace Modules\Reunioes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReuniaoLink extends Model
{
    protected $table = 'reuniao_links';

    protected $fillable = [
        'reuniao_id',
        'url',
        'plataforma',
        'audiencia_publica',
    ];

    protected $casts = [
        'audiencia_publica' => 'boolean',
    ];

    public function reuniao(): BelongsTo
    {
        return $this->belongsTo(Reuniao::class);
    }
}
