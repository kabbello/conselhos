<?php

namespace Modules\Resolucoes\Services;

use Illuminate\Support\Facades\DB;
use Modules\Conselhos\Models\Conselho;
use Modules\Resolucoes\Models\Processo;

/**
 * Regras de negócio para processos que tramitam no conselho.
 *
 * RN-P01: Numeração automática — "{NNN}/{ANO}" única por conselho/ano.
 * RN-P02: Transições de status seguem o fluxo configurado para o conselho. A Lei 9.784/99
 *          regula o processo administrativo federal; aplicação municipal depende do ordenamento local.
 * RN-P03: Um processo só pode ser encerrado em status terminal.
 */
class ProcessoService
{
    /**
     * RN-P01: Gera o próximo número de processo para o conselho/ano.
     * Sequencial por conselho + ano, formatado com 3 dígitos.
     */
    public function proximoNumero(Conselho $conselho, int $ano): int
    {
        // A12: lock pessimista para evitar race condition em criação concorrente.
        // Retorna inteiro puro; o accessor numero_completo do modelo formata "NNN/ANO".
        return (int) Processo::withTrashed()
            ->where('conselho_id', $conselho->id)
            ->where('ano', $ano)
            ->whereNotNull('numero')
            ->lockForUpdate()
            ->max('numero') + 1;
    }

    /**
     * Cria um novo processo com número automático (dentro de transação).
     */
    public function abrir(Conselho $conselho, array $dados): Processo
    {
        return DB::transaction(function () use ($conselho, $dados) {
            $ano = $dados['ano'] ?? now()->year;

            if (empty($dados['numero'])) {
                $dados['numero'] = $this->proximoNumero($conselho, $ano);
            }

            return $conselho->processos()->create([
                ...$dados,
                'status'        => $dados['status'] ?? 'ABERTO',
                'data_abertura' => $dados['data_abertura'] ?? now()->toDateString(),
            ]);
        });
    }

    /**
     * RN-P02: Avança o status do processo para o próximo estado válido.
     *
     * @throws \InvalidArgumentException se a transição não for permitida
     */
    public function avancarStatus(Processo $processo, string $novoStatus): Processo
    {
        $transicoesPermitidas = [
            'ABERTO'                    => ['EM_ANALISE', 'ARQUIVADO'],
            'EM_ANALISE'                => ['AGUARDANDO_COMPLEMENTACAO', 'ENCAMINHADO_COMISSAO', 'VOTADO', 'ARQUIVADO'],
            'AGUARDANDO_COMPLEMENTACAO' => ['EM_ANALISE', 'ARQUIVADO'],
            'ENCAMINHADO_COMISSAO'      => ['EM_ANALISE', 'VOTADO', 'ARQUIVADO'],
            'VOTADO'                    => ['APROVADO', 'REJEITADO'],
            'APROVADO'                  => ['ARQUIVADO'],
            'REJEITADO'                 => ['ARQUIVADO'],
            'ARQUIVADO'                 => [],
        ];

        $statusAtual = $processo->status;
        $permitidos = $transicoesPermitidas[$statusAtual] ?? [];

        if (! in_array($novoStatus, $permitidos)) {
            throw new \InvalidArgumentException(
                "Transição de status inválida: '{$statusAtual}' → '{$novoStatus}'. " .
                'Permitidos: ' . (empty($permitidos) ? 'nenhum (status terminal)' : implode(', ', $permitidos))
            );
        }

        $dados = ['status' => $novoStatus];

        if (in_array($novoStatus, ['APROVADO', 'REJEITADO', 'ARQUIVADO'])) {
            $dados['data_encerramento'] = now()->toDateString();
        }

        $processo->update($dados);

        return $processo->fresh();
    }

    /**
     * RN-P03: Encerra um processo em status terminal com motivo em observações.
     */
    public function arquivar(Processo $processo, ?string $motivo = null): Processo
    {
        return $this->avancarStatus(
            $processo->status === 'ABERTO'
                ? tap($processo, fn ($p) => $p->update(['status' => 'EM_ANALISE']))
                : $processo,
            'ARQUIVADO'
        );
    }
}
