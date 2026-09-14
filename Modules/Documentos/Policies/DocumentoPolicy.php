<?php

namespace Modules\Documentos\Policies;

use App\Models\User;
use Modules\Documentos\Models\Documento;

class DocumentoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['view.documentos', 'view-privados.documentos']);
    }

    public function view(User $user, Documento $documento): bool
    {
        if (! $this->doMunicipio($user, $documento)) {
            return false;
        }

        if ($documento->publico) {
            return $user->hasAnyPermission(['view.documentos', 'view-privados.documentos']);
        }

        return $user->hasPermissionTo('view-privados.documentos');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create.documentos');
    }

    public function update(User $user, Documento $documento): bool
    {
        return $user->hasPermissionTo('update.documentos')
            && $this->doMunicipio($user, $documento);
    }

    public function delete(User $user, Documento $documento): bool
    {
        return $user->hasPermissionTo('delete.documentos')
            && $this->doMunicipio($user, $documento);
    }

    public function publicar(User $user, Documento $documento): bool
    {
        return $user->hasPermissionTo('publicar.documentos')
            && $this->doMunicipio($user, $documento);
    }

    // ---------- Helper ----------

    /**
     * Verifica que o usuário tem acesso ao conselho do documento.
     * super_admin nunca chega aqui (Gate::before retorna true antes).
     *
     * - admin_municipal: acesso a todos os conselhos do município
     * - gestor_conselho: acesso apenas ao(s) conselho(s) explicitamente atribuídos
     * - demais: restrição pelo município (escopo do Resource já filtra a lista)
     */
    private function doMunicipio(User $user, Documento $documento): bool
    {
        $conselho = $documento->conselho;
        if (! $conselho) {
            return false;
        }

        if ($user->hasRole('gestor_conselho')) {
            return $user->gerenciaConselho($conselho->id);
        }

        return (int) $user->municipio_id === (int) $conselho->municipio_id;
    }
}
