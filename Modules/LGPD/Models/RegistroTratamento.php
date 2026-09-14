<?php

namespace Modules\LGPD\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Municipios\Models\Municipio;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class RegistroTratamento extends Model
{
    use LogsActivity;

    protected $table = 'registro_tratamentos';

    protected $fillable = [
        'municipio_id',
        'controlador',
        'operadores',
        'sistemas',
        'origem_dados',
        'atividade',
        'finalidade',
        'dados_pessoais',
        'dados_sensiveis',
        'categorias_titulares',
        'envolve_criancas',
        'base_legal',
        'base_legal_sensiveis',
        'prazo_retencao',
        'metodo_descarte',
        'responsavel_revisao',
        'data_proxima_revisao',
        'destinatarios',
        'transferencia_internacional',
        'medidas_seguranca',
        'observacoes_ripd',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'transferencia_internacional' => 'boolean',
            'dados_sensiveis'             => 'boolean',
            'envolve_criancas'            => 'boolean',
            'ativo'                       => 'boolean',
            'data_proxima_revisao'        => 'date',
        ];
    }

    public static function baseLegalSensiveisLabel(string $base): string
    {
        return match ($base) {
            'tutela_saude'               => 'Tutela da saúde (Art. 11, II, f)',
            'obrigacao_legal'            => 'Cumprimento de obrigação legal (Art. 11, II, a)',
            'politica_publica'           => 'Políticas públicas (Art. 11, II, b)',
            'exercicio_regular_direitos' => 'Exercício regular de direitos (Art. 11, II, d)',
            'prevencao_fraude'           => 'Prevenção à fraude (Art. 11, II, g)',
            default                      => $base,
        };
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable();
    }

    public static function baseLegalLabel(string $base): string
    {
        return match ($base) {
            'consentimento'              => 'Consentimento do titular',
            'contrato'                   => 'Execução de contrato',
            'obrigacao_legal'            => 'Cumprimento de obrigação legal',
            'exercicio_regular_direitos' => 'Exercício regular de direitos',
            'protecao_vida'              => 'Proteção da vida',
            'tutela_saude'               => 'Tutela da saúde',
            'interesse_legitimo'         => 'Interesse legítimo',
            'protecao_credito'           => 'Proteção ao crédito',
            'politica_publica'           => 'Execução de políticas públicas',
            default                      => $base,
        };
    }

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }
}
