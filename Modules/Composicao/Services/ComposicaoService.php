<?php

namespace Modules\Composicao\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Composicao\Models\Composicao;
use Modules\Composicao\Models\Conselheiro;
use Modules\Conselhos\Models\Conselho;

/**
 * Regras de negócio para composição de conselhos.
 *
 * RN-001  Adicionar membro
 * RN-002  Encerrar mandato
 * RN-003  Promover membro a PRESIDENTE ou SECRETARIO
 */
class ComposicaoService
{
    // ──────────────────────────────────────────────
    // RN-001  Adicionar membro à composição
    // ──────────────────────────────────────────────

    /**
     * @param  array{
     *   conselheiro_id: int|null,
     *   entidade_id: int|null,
     *   cargo_id: int|null,
     *   tipo: string,
     *   nome_exibicao: string|null,
     *   email_exibicao: string|null,
     *   telefone_contato: string|null,
     *   data_nomeacao: string|null,
     *   data_fim: string|null,
     *   decreto_nomeacao: string|null,
     *   observacoes: string|null,
     * } $dados
     */
    public function adicionar(Conselho $conselho, array $dados): Composicao
    {
        $this->validarTipoUnico($conselho, $dados['tipo'], $dados['conselheiro_id'] ?? null);

        return DB::transaction(function () use ($conselho, $dados) {
            if (in_array($dados['tipo'], ['PRESIDENTE', 'SECRETARIO'])) {
                $this->encerrarTitularAtual($conselho, $dados['tipo']);
            }

            $composicao = Composicao::create(array_merge($dados, [
                'conselho_id' => $conselho->id,
                'ativo'       => true,
            ]));

            if ($composicao->conselheiro_id && ! $composicao->nome_exibicao) {
                $conselheiro = Conselheiro::find($composicao->conselheiro_id);
                // C03: preencher apenas o nome; email_exibicao deve ser o contato institucional,
                // informado explicitamente — nunca copiado do e-mail pessoal do conselheiro.
                $composicao->update([
                    'nome_exibicao' => $conselheiro?->nome,
                ]);
            }

            return $composicao->fresh();
        });
    }

    // ──────────────────────────────────────────────
    // RN-002  Encerrar mandato
    // ──────────────────────────────────────────────

    public function encerrar(Composicao $composicao, ?string $dataFim = null, ?string $motivo = null): Composicao
    {
        if (! $composicao->ativo) {
            throw ValidationException::withMessages([
                'composicao' => 'Este mandato já está encerrado.',
            ]);
        }

        DB::transaction(function () use ($composicao, $dataFim, $motivo) {
            $updates = [
                'ativo'    => false,
                'data_fim' => $dataFim ?? now()->toDateString(),
            ];

            if ($motivo) {
                $obs = $composicao->observacoes
                    ? $composicao->observacoes . "\n[Encerramento] " . $motivo
                    : '[Encerramento] ' . $motivo;
                $updates['observacoes'] = $obs;
            }

            $composicao->update($updates);
            $composicao->delete();
        });

        return $composicao->fresh(['conselheiro', 'conselho']);
    }

    // ──────────────────────────────────────────────
    // RN-003  Promover membro
    // ──────────────────────────────────────────────

    /**
     * @param  'PRESIDENTE'|'SECRETARIO' $novoTipo
     */
    public function promover(Composicao $composicao, string $novoTipo): Composicao
    {
        if (! in_array($novoTipo, ['PRESIDENTE', 'SECRETARIO'])) {
            throw ValidationException::withMessages([
                'tipo' => "Promoção inválida: apenas PRESIDENTE ou SECRETARIO são permitidos.",
            ]);
        }

        if (! $composicao->ativo) {
            throw ValidationException::withMessages([
                'composicao' => 'Não é possível promover um membro inativo.',
            ]);
        }

        $conselho = $composicao->conselho;

        DB::transaction(function () use ($composicao, $conselho, $novoTipo) {
            $this->encerrarTitularAtual($conselho, $novoTipo, rebaixar: true);
            $composicao->update(['tipo' => $novoTipo]);
        });

        return $composicao->fresh();
    }

    // ──────────────────────────────────────────────
    // Helpers privados
    // ──────────────────────────────────────────────

    private function validarTipoUnico(Conselho $conselho, string $tipo, ?int $conselheiro_id): void
    {
        if (! $conselheiro_id) {
            return;
        }

        $existe = Composicao::where('conselho_id', $conselho->id)
            ->where('conselheiro_id', $conselheiro_id)
            ->where('tipo', $tipo)
            ->where('ativo', true)
            ->whereNull('deleted_at')
            ->exists();

        if ($existe) {
            throw ValidationException::withMessages([
                'conselheiro_id' => "Este conselheiro já possui o tipo '{$tipo}' ativo neste conselho.",
            ]);
        }
    }

    private function encerrarTitularAtual(Conselho $conselho, string $tipo, bool $rebaixar = false): void
    {
        $titular = Composicao::where('conselho_id', $conselho->id)
            ->where('tipo', $tipo)
            ->where('ativo', true)
            ->whereNull('deleted_at')
            ->first();

        if (! $titular) {
            return;
        }

        if ($rebaixar) {
            $titular->update(['tipo' => 'MEMBRO']);
        } else {
            $titular->update(['ativo' => false, 'data_fim' => now()->toDateString()]);
            $titular->delete();
        }
    }
}
