<?php

namespace Modules\Comissoes\Policies;

use App\Models\User;
use Modules\Comissoes\Models\Comissao;

class ComissaoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['view.comissoes', 'create.comissoes', 'update.comissoes']);
    }

    public function view(User $user, Comissao $comissao): bool
    {
        return $user->hasAnyPermission(['view.comissoes', 'create.comissoes', 'update.comissoes'])
            && $this->doMunicipio($user, $comissao);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create.comissoes');
    }

    public function update(User $user, Comissao $comissao): bool
    {
        return $user->hasPermissionTo('update.comissoes')
            && $this->doMunicipio($user, $comissao);
    }

    public function delete(User $user, Comissao $comissao): bool
    {
        return $user->hasPermissionTo('delete.comissoes')
            && $this->doMunicipio($user, $comissao);
    }

    public function encerrar(User $user, Comissao $comissao): bool
    {
        return $user->hasPermissionTo('update.comissoes')
            && $comissao->isAtiva()
            && $this->doMunicipio($user, $comissao);
    }

    // ---------- Helper ----------

    /**
     * Verifica que o usuário tem acesso ao conselho da comissão.
     * super_admin nunca chega aqui (Gate::before retorna true antes).
     *
     * - admin_municipal: acesso a todos os conselhos do município
     * - gestor_conselho: acesso apenas ao(s) conselho(s) explicitamente atribuídos
     * - demais: restrição pelo município (escopo do Resource já filtra a lista)
     */
    private function doMunicipio(User $user, Comissao $comissao): bool
    {
        $conselho = $comissao->conselho;
        if (! $conselho) {
            return false;
        }

        if ($user->hasRole('gestor_conselho')) {
            return $user->gerenciaConselho($conselho->id);
        }

        return (int) $user->municipio_id === (int) $conselho->municipio_id;
    }
}
