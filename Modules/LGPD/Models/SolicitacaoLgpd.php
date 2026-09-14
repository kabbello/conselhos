<?php

namespace Modules\LGPD\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Modules\Municipios\Models\Municipio;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SolicitacaoLgpd extends Model
{
    use LogsActivity, SoftDeletes;

    protected $table = 'solicitacoes_lgpd';

    protected $fillable = [
        'municipio_id',
        'protocolo',
        'nome_titular',
        'email_titular',
        'cpf_titular',
        'tipo',
        'descricao',
        'status',
        'resposta',
        'prazo_legal',
        'atendida_em',
        'atendida_por',
    ];

    protected function casts(): array
    {
        return [
            'prazo_legal'  => 'date',
            'atendida_em'  => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        // A04: não logar 'resposta' — contém dados pessoais do titular
        return LogOptions::defaults()->logOnly(['status', 'atendida_por', 'prazo_legal']);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * A04: prazo de referência interno por tipo de direito.
     * Confirmação/acesso (Art. 19): até 15 dias.
     * Demais direitos: 30 dias de referência para triagem e resposta fundamentada.
     * O prazo real depende da complexidade, exceções legais e decisão do encarregado.
     */
    public static function prazoDias(string $tipo): int
    {
        return match ($tipo) {
            'acesso', 'informacao' => 15,
            default                => 30,
        };
    }

    public function isAtrasada(): bool
    {
        return $this->prazo_legal->isPast() && ! in_array($this->status, ['atendida', 'negada', 'arquivada']);
    }

    /**
     * A12: gera o próximo número de protocolo dentro de uma transação ativa.
     *
     * IMPORTANTE: este método deve ser chamado DENTRO de uma transação DB::transaction()
     * que também insira o registro. O lockForUpdate segura a linha apenas enquanto a
     * transação externa estiver aberta — se o lock for liberado antes do INSERT, dois
     * requests concorrentes podem receber a mesma sequência.
     *
     * Usa COUNT para evitar SUBSTRING_INDEX (MySQL-only), que quebra em SQLite nos testes.
     * withTrashed() garante que registros soft-deleted não causem reutilização de número.
     */
    public static function gerarProtocoloNovaSequencia(int $municipioId): string
    {
        $ano = now()->year;

        $seq = static::withTrashed()
                ->where('municipio_id', $municipioId)
                ->whereYear('created_at', $ano)
                ->lockForUpdate()
                ->count() + 1;

        return sprintf('LGPD-%d-%04d', $ano, $seq);
    }

    // ── Labels ───────────────────────────────────────────────────────────────

    public static function tipoLabel(string $tipo): string
    {
        return match ($tipo) {
            'acesso'                   => 'Acesso aos dados',
            'correcao'                 => 'Correção de dados',
            'exclusao'                 => 'Eliminação de dados',
            'portabilidade'            => 'Portabilidade',
            'oposicao'                 => 'Oposição ao tratamento',
            'revogacao_consentimento'  => 'Revogação de consentimento',
            'informacao'               => 'Informação sobre tratamento',
            'outro'                    => 'Outro',
            default                    => $tipo,
        };
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'pendente'        => 'Pendente',
            'em_atendimento'  => 'Em atendimento',
            'atendida'        => 'Atendida',
            'negada'          => 'Negada',
            'arquivada'       => 'Arquivada',
            default           => $status,
        };
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    public function atendidaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atendida_por');
    }
}
