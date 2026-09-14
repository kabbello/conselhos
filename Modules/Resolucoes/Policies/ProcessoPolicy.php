<?php

namespace Modules\Resolucoes\Policies;

use App\Models\User;
use Modules\Resolucoes\Models\Processo;

class ProcessoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view.processos');
    }

    public function view(User $user, Processo $processo): bool
    {
        return $user->hasPermissionTo('view.processos')
            && $this->doMunicipio($user, $processo);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create.processos');
    }

    public function update(User $user, Processo $processo): bool
    {
        return $user->hasPermissionTo('update.processos')
            && ! $processo->isEncerrado()
            && $this->doMunicipio($user, $processo);
    }

    public function delete(User $user, Processo $processo): bool
    {
        return $user->hasPermissionTo('delete.processos')
            && $this->doMunicipio($user, $processo);
    }

    public function encerrar(User $user, Processo $processo): bool
    {
        return $user->hasPermissionTo('update.processos')
            && ! $processo->isEncerrado()
            && $this->doMunicipio($user, $processo);
    }

    // ---------- Helper ----------

    /**
     * Verifica que o usuário tem acesso ao conselho do processo.
     * super_admin nunca chega aqui (Gate::before retorna true antes).
     *
     * - admin_municipal: acesso a todos os conselhos do município
     * - gestor_conselho: acesso apenas ao(s) conselho(s) explicitamente atribuídos
     * - demais: restrição pelo município (escopo do Resource já filtra a lista)
     */
    private function doMunicipio(User $user, Processo $processo): bool
    {
        $conselho = $processo->conselho;
        if (! $conselho) {
            return false;
        }

        if ($user->hasRole('gestor_conselho')) {
            return $user->gerenciaConselho($conselho->id);
        }

        return (int) $user->municipio_id === (int) $conselho->municipio_id;
    }
}
