<?php

namespace Modules\Resolucoes\Services;

use Illuminate\Support\Facades\DB;
use Modules\Conselhos\Models\Conselho;
use Modules\Resolucoes\Models\AtoNormativo;

/**
 * Regras de negócio para atos normativos produzidos pelos conselhos.
 *
 * RN-A01: Numeração automática — sequencial por tipo+ano, único no conselho.
 * RN-A02: Um ato só pode ser publicado se estiver em status APROVADO ou VIGENTE.
 * RN-A03: Ao publicar, status muda para VIGENTE automaticamente.
 * RN-A04: Revogação em cadeia — ao revogar um ato, seu antecessor é marcado REVOGADO.
 *
 * Base legal: Lei 12.527/2011 (LAI) — publicação obrigatória.
 */
class AtoNormativoService
{
    /**
     * RN-A01: Próximo número de ato para o tipo+conselho+ano.
     * Cada tipo tem sua própria sequência (Resolução 001/2025 e Portaria 001/2025 são distintos).
     */
    public function proximoNumero(Conselho $conselho, string $tipo, int $ano): int
    {
        // A12: lock pessimista para evitar race condition em criação concorrente
        return (int) AtoNormativo::withTrashed()
            ->where('conselho_id', $conselho->id)
            ->where('tipo', $tipo)
            ->where('ano', $ano)
            ->lockForUpdate()
            ->max('numero') + 1;
    }

    /**
     * Cria um novo ato normativo com número automático (dentro de transação).
     */
    public function criar(Conselho $conselho, array $dados): AtoNormativo
    {
        return DB::transaction(function () use ($conselho, $dados) {
            $ano  = $dados['ano'] ?? now()->year;
            $tipo = $dados['tipo'];

            if (empty($dados['numero'])) {
                $dados['numero'] = $this->proximoNumero($conselho, $tipo, $ano);
            }

            return $conselho->atosNormativos()->create([
                ...$dados,
                'status' => $dados['status'] ?? 'RASCUNHO',
            ]);
        });
    }

    /**
     * RN-A02 + RN-A03: Publica o ato (status → VIGENTE, publicado = true).
     *
     * @throws \RuntimeException se o ato não estiver em status publicável
     */
    public function publicar(AtoNormativo $ato, ?string $referenciaDiarioOficial = null): AtoNormativo
    {
        if (! in_array($ato->status, ['APROVADO', 'VIGENTE'])) {
            throw new \RuntimeException(
                "Ato normativo em status '{$ato->status}' não pode ser publicado. " .
                'É necessário que esteja APROVADO ou VIGENTE.'
            );
        }

        $ato->update([
            'publicado'                  => true,
            'status'                     => 'VIGENTE',
            'data_publicacao'            => $ato->data_publicacao ?? now()->toDateString(),
            'diario_oficial_referencia'  => $referenciaDiarioOficial ?? $ato->diario_oficial_referencia,
        ]);

        return $ato->fresh();
    }

    /**
     * RN-A04: Revoga este ato e emite um novo ato como seu substituto.
     *
     * O ato revogador deve apontar para o revogado via `revoga_id`.
     * O hook `saved` em AtoNormativo já atualiza o `revogado_por_id` e status automaticamente.
     *
     * @throws \RuntimeException se o ato alvo não estiver VIGENTE
     */
    public function revogar(AtoNormativo $atoRevogado, AtoNormativo $atoRevogador): AtoNormativo
    {
        if (! $atoRevogado->isVigente()) {
            throw new \RuntimeException(
                "O ato {$atoRevogado->label_completo} não está VIGENTE e não pode ser revogado."
            );
        }

        if ($atoRevogador->revoga_id !== $atoRevogado->id) {
            $atoRevogador->update(['revoga_id' => $atoRevogado->id]);
        }

        return $atoRevogado->fresh();
    }

    /**
     * Suspende um ato vigente (ex.: por decisão judicial).
     *
     * @throws \RuntimeException se o ato não estiver VIGENTE
     */
    public function suspender(AtoNormativo $ato, string $motivo): AtoNormativo
    {
        if (! $ato->isVigente()) {
            throw new \RuntimeException(
                "Apenas atos VIGENTES podem ser suspensos. Status atual: '{$ato->status}'."
            );
        }

        $ato->update([
            'status'      => 'SUSPENSO',
            'observacoes' => trim(($ato->observacoes ? $ato->observacoes . "\n\n" : '') . "SUSPENSO: {$motivo}"),
        ]);

        return $ato->fresh();
    }
}
