<?php

namespace Modules\Conselhos\Policies;

use App\Models\User;
use Modules\Conselhos\Models\Conselho;

class ConselhoPolicy
{
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
