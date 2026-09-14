<?php

namespace Modules\Resolucoes\Policies;

use App\Models\User;
use Modules\Resolucoes\Models\AtoNormativo;

class AtoNormativoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view.atos-normativos');
    }

    public function view(User $user, AtoNormativo $ato): bool
    {
        return $user->hasPermissionTo('view.atos-normativos')
            && $this->doMunicipio($user, $ato);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create.atos-normativos');
    }

    public function update(User $user, AtoNormativo $ato): bool
    {
        return $user->hasPermissionTo('update.atos-normativos')
            && $ato->status !== 'REVOGADO'
            && $this->doMunicipio($user, $ato);
    }

    public function delete(User $user, AtoNormativo $ato): bool
    {
        return $user->hasPermissionTo('delete.atos-normativos')
            && $this->doMunicipio($user, $ato);
    }

    public function publicar(User $user, AtoNormativo $ato): bool
    {
        return $user->hasPermissionTo('publicar.atos-normativos')
            && ! $ato->publicado
            && $this->doMunicipio($user, $ato);
    }

    public function revogar(User $user, AtoNormativo $ato): bool
    {
        return $user->hasPermissionTo('update.atos-normativos')
            && $ato->isVigente()
            && $this->doMunicipio($user, $ato);
    }

    // ---------- Helper ----------

    /**
     * Verifica que o usuário tem acesso ao conselho do ato normativo.
     * super_admin nunca chega aqui (Gate::before retorna true antes).
     *
     * - admin_municipal: acesso a todos os conselhos do município
     * - gestor_conselho: acesso apenas ao(s) conselho(s) explicitamente atribuídos
     * - demais: restrição pelo município (escopo do Resource já filtra a lista)
     */
    private function doMunicipio(User $user, AtoNormativo $ato): bool
    {
        $conselho = $ato->conselho;
        if (! $conselho) {
            return false;
        }

        if ($user->hasRole('gestor_conselho')) {
            return $user->gerenciaConselho($conselho->id);
        }

        return (int) $user->municipio_id === (int) $conselho->municipio_id;
    }
}
