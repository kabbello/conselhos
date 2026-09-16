<?php

namespace Modules\Composicao\Observers;

use Illuminate\Support\Facades\DB;
use Modules\Composicao\Models\Composicao;
use Spatie\Permission\Models\Role;

/**
 * Formaliza a regra: PRESIDENTE ativo = gestor natural do conselho.
 *
 * Ao salvar:
 *  - Se tipo=PRESIDENTE + ativo=true + conselheiro tem user_id
 *    → upsert em user_conselho_gestores + atribui role gestor_conselho
 *
 * Ao salvar (inativar/trocar tipo) ou deletar (soft/force):
 *  - Se o vínculo como PRESIDENTE foi removido
 *    → revoga em user_conselho_gestores
 *    → remove role gestor_conselho se não gerir mais nenhum conselho ativo
 */
class ComposicaoObserver
{
    public function saved(Composicao $composicao): void
    {
        $userId = $composicao->conselheiro?->user_id;

        if (! $userId) {
            return;
        }

        $ePresidenteAtivo = $composicao->tipo === 'PRESIDENTE' && $composicao->ativo && ! $composicao->trashed();

        if ($ePresidenteAtivo) {
            $this->vincularGestor($userId, $composicao->conselho_id);
        } else {
            // Verifica se antes era PRESIDENTE para decidir revogar
            $this->revogarSeNecessario($userId, $composicao->conselho_id);
        }
    }

    public function deleted(Composicao $composicao): void
    {
        $userId = $composicao->conselheiro?->user_id;

        if (! $userId) {
            return;
        }

        if ($composicao->tipo === 'PRESIDENTE') {
            $this->revogarSeNecessario($userId, $composicao->conselho_id);
        }
    }

    public function forceDeleted(Composicao $composicao): void
    {
        $this->deleted($composicao);
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function vincularGestor(int $userId, int $conselhoId): void
    {
        DB::table('user_conselho_gestores')->upsert(
            [
                'user_id'      => $userId,
                'conselho_id'  => $conselhoId,
                'atribuido_por' => null,
                'atribuido_em' => now(),
                'revogado_em'  => null,
            ],
            ['user_id', 'conselho_id'],          // unique key
            ['revogado_em', 'atribuido_em'],     // atualiza caso já existia revogado
        );

        $user = \App\Models\User::find($userId);

        if ($user && ! $user->hasRole('gestor_conselho')) {
            $user->assignRole('gestor_conselho');

            activity('composicao-observer')
                ->causedBy(null)
                ->performedOn($user)
                ->withProperties(['conselho_id' => $conselhoId])
                ->event('gestor_natural_vinculado')
                ->log("Presidente vinculado automaticamente como gestor do conselho #{$conselhoId}");
        }
    }

    private function revogarSeNecessario(int $userId, int $conselhoId): void
    {
        $atualizado = DB::table('user_conselho_gestores')
            ->where('user_id', $userId)
            ->where('conselho_id', $conselhoId)
            ->whereNull('revogado_em')
            ->update(['revogado_em' => now()]);

        if (! $atualizado) {
            return;
        }

        // Verifica se ainda gere outros conselhos ativos
        $ainGere = DB::table('user_conselho_gestores')
            ->where('user_id', $userId)
            ->whereNull('revogado_em')
            ->exists();

        if (! $ainGere) {
            $user = \App\Models\User::find($userId);
            $user?->removeRole('gestor_conselho');

            activity('composicao-observer')
                ->causedBy(null)
                ->performedOn($user)
                ->withProperties(['conselho_id' => $conselhoId])
                ->event('gestor_natural_revogado')
                ->log("Role gestor_conselho removido: usuário não é mais presidente de nenhum conselho ativo");
        } else {
            activity('composicao-observer')
                ->causedBy(null)
                ->performedOn(\App\Models\User::find($userId))
                ->withProperties(['conselho_id' => $conselhoId])
                ->event('gestor_natural_revogado')
                ->log("Vínculo de gestor revogado para conselho #{$conselhoId} (mantém outros conselhos)");
        }
    }
}
