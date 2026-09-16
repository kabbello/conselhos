<?php

namespace Modules\Reunioes\Policies;

use App\Models\User;
use Modules\Reunioes\Models\Reuniao;

class ReuniaoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view.reunioes');
    }

    public function view(User $user, Reuniao $reuniao): bool
    {
        return $user->hasPermissionTo('view.reunioes')
            && $this->doMunicipio($user, $reuniao);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create.reunioes');
    }

    public function update(User $user, Reuniao $reuniao): bool
    {
        return $user->hasPermissionTo('update.reunioes')
            && $this->doMunicipio($user, $reuniao);
    }

    public function delete(User $user, Reuniao $reuniao): bool
    {
        return $user->hasPermissionTo('cancelar.reunioes')
            && $reuniao->isAgendada()
            && $this->doMunicipio($user, $reuniao);
    }

    public function registrarPresenca(User $user, Reuniao $reuniao): bool
    {
        return $user->hasPermissionTo('registrar-presenca.reunioes')
            && $reuniao->isRealizada()
            && $this->doMunicipio($user, $reuniao);
    }

    public function notificar(User $user, Reuniao $reuniao): bool
    {
        return $user->hasPermissionTo('enviar-comunicacao.reunioes')
            && $this->doMunicipio($user, $reuniao);
    }

    public function gerenciarLink(User $user, Reuniao $reuniao): bool
    {
        return $user->hasPermissionTo('gerenciar-link.reunioes')
            && $this->doMunicipio($user, $reuniao);
    }

    public function gerenciarAnexos(User $user, Reuniao $reuniao): bool
    {
        return $user->hasPermissionTo('gerenciar-anexos.reunioes')
            && $this->doMunicipio($user, $reuniao);
    }

    public function aprovarAta(User $user, Reuniao $reuniao): bool
    {
        return $user->hasPermissionTo('aprovar-ata.reunioes')
            && $reuniao->isRealizada()
            && $this->doMunicipio($user, $reuniao);
    }

    // ---------- Helper ----------

    /**
     * Verifica que o usuário tem acesso ao conselho da reunião.
     * super_admin nunca chega aqui (Gate::before retorna true antes).
     *
     * - admin_municipal: acesso a todos os conselhos do município
     * - gestor_conselho: acesso apenas ao(s) conselho(s) explicitamente atribuídos
     * - demais: restrição pelo município (escopo do Resource já filtra a lista)
     */
    private function doMunicipio(User $user, Reuniao $reuniao): bool
    {
        $conselho = $reuniao->conselho;
        if (! $conselho) {
            return false;
        }

        if ($user->hasRole('gestor_conselho')) {
            return $user->gerenciaConselho($conselho->id);
        }

        return (int) $user->municipio_id === (int) $conselho->municipio_id;
    }
}
