<?php

namespace Modules\Composicao\Policies;

use App\Models\User;
use Modules\Composicao\Models\Composicao;
use Modules\Conselhos\Models\Conselho;

class ComposicaoPolicy
{
    public function view(User $user, Conselho $conselho): bool
    {
        if ($user->hasRole('admin_municipal')) {
            return $user->municipio_id === $conselho->municipio_id;
        }

        return $this->isMembroAtivo($user, $conselho);
    }

    public function create(User $user, Conselho $conselho): bool
    {
        return $this->podeGerenciar($user, $conselho);
    }

    public function update(User $user, Composicao $composicao): bool
    {
        return $this->podeGerenciar($user, $composicao->conselho);
    }

    public function inativar(User $user, Composicao $composicao): bool
    {
        return $this->podeGerenciar($user, $composicao->conselho);
    }

    public function promover(User $user, Conselho $conselho): bool
    {
        if ($user->hasRole('admin_municipal')) {
            return $user->municipio_id === $conselho->municipio_id;
        }

        return $this->isPresidenteAtivo($user, $conselho);
    }

    public function importar(User $user, Conselho $conselho): bool
    {
        return $this->podeGerenciar($user, $conselho);
    }

    private function podeGerenciar(User $user, Conselho $conselho): bool
    {
        if ($user->hasRole('admin_municipal')) {
            return $user->municipio_id === $conselho->municipio_id;
        }

        return $this->isGestorAtivo($user, $conselho);
    }

    private function isGestorAtivo(User $user, Conselho $conselho): bool
    {
        if (! $user->conselheiro) {
            return false;
        }

        return Composicao::where('conselho_id', $conselho->id)
            ->where('conselheiro_id', $user->conselheiro->id)
            ->whereIn('tipo', ['PRESIDENTE', 'SECRETARIO'])
            ->where('ativo', true)
            ->whereNull('deleted_at')
            ->exists();
    }

    private function isPresidenteAtivo(User $user, Conselho $conselho): bool
    {
        if (! $user->conselheiro) {
            return false;
        }

        return Composicao::where('conselho_id', $conselho->id)
            ->where('conselheiro_id', $user->conselheiro->id)
            ->where('tipo', 'PRESIDENTE')
            ->where('ativo', true)
            ->whereNull('deleted_at')
            ->exists();
    }

    private function isMembroAtivo(User $user, Conselho $conselho): bool
    {
        if (! $user->conselheiro) {
            return false;
        }

        return Composicao::where('conselho_id', $conselho->id)
            ->where('conselheiro_id', $user->conselheiro->id)
            ->where('ativo', true)
            ->whereNull('deleted_at')
            ->exists();
    }
}
