<?php

namespace App\Policies;

use App\Models\Conselho;
use App\Models\User;

/**
 * Regras de acesso para conselhos.
 *
 * Gestão do cadastro do conselho (nome, tipo, configurações):
 * - super_admin: sempre (via Gate::before)
 * - admin_municipal: apenas conselhos do próprio município
 * - Outros: sem acesso de escrita
 */
class ConselhoPolicy
{
    // super_admin passa por tudo via Gate::before() no AuthServiceProvider

    public function view(User $user, Conselho $conselho): bool
    {
        return $user->municipio_id === $conselho->municipio_id;
    }

    public function viewAny(User $user): bool
    {
        return $user->municipio_id !== null;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin_municipal');
    }

    public function update(User $user, Conselho $conselho): bool
    {
        if (! $user->hasRole('admin_municipal')) {
            return false;
        }

        return $user->municipio_id === $conselho->municipio_id;
    }

    public function delete(User $user, Conselho $conselho): bool
    {
        if (! $user->hasRole('admin_municipal')) {
            return false;
        }

        return $user->municipio_id === $conselho->municipio_id;
    }
}
