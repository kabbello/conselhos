<?php

namespace App\Policies;

use App\Models\Composicao;
use App\Models\Conselho;
use App\Models\User;

/**
 * Regras de acesso para composição de conselhos.
 *
 * Modelo de acesso:
 * - Um conselheiro tem UM login (users) e pode pertencer a N conselhos
 * - O acesso a cada conselho é determinado pela linha correspondente em composicao
 * - Áreas públicas (lista de membros) não passam por esta Policy
 * - Esta Policy governa ações de gestão (adicionar/editar/inativar membros)
 *
 * Quem pode promover alguém a PRESIDENTE ou SECRETARIO:
 * - super_admin: sempre
 * - PRESIDENTE ativo do conselho em questão: sim
 * - admin_municipal: sim (gestão administrativa)
 * - Outros: não
 */
class ComposicaoPolicy
{
    // super_admin passa por tudo via Gate::before() no AuthServiceProvider

    /**
     * Ver composição de um conselho (área restrita — dados de contato).
     * A listagem pública é feita no portal sem autenticação.
     */
    public function view(User $user, Conselho $conselho): bool
    {
        if ($user->hasRole('admin_municipal')) {
            return $user->municipio_id === $conselho->municipio_id;
        }

        // Qualquer membro ativo do conselho pode ver a composição restrita
        return $this->isMembroAtivo($user, $conselho);
    }

    /**
     * Adicionar membro à composição.
     */
    public function create(User $user, Conselho $conselho): bool
    {
        return $this->podeGerenciar($user, $conselho);
    }

    /**
     * Editar dados de um membro (observações, entidade, cargo, datas).
     */
    public function update(User $user, Composicao $composicao): bool
    {
        $conselho = $composicao->conselho;
        return $this->podeGerenciar($user, $conselho);
    }

    /**
     * Inativar / encerrar mandato de um membro.
     */
    public function inativar(User $user, Composicao $composicao): bool
    {
        $conselho = $composicao->conselho;
        return $this->podeGerenciar($user, $conselho);
    }

    /**
     * Promover membro a PRESIDENTE ou SECRETARIO.
     *
     * Regra: apenas super_admin (via Gate::before), admin_municipal,
     * ou o PRESIDENTE ATIVO do conselho em questão.
     */
    public function promover(User $user, Conselho $conselho): bool
    {
        if ($user->hasRole('admin_municipal')) {
            return $user->municipio_id === $conselho->municipio_id;
        }

        if ($user->hasRole('gestor_conselho')) {
            return $user->gerenciaConselho($conselho->id);
        }

        return $this->isPresidenteAtivo($user, $conselho);
    }

    /**
     * Importar composição via decreto (preview + apply).
     */
    public function importar(User $user, Conselho $conselho): bool
    {
        return $this->podeGerenciar($user, $conselho);
    }

    // ---------- Helpers privados ----------

    /**
     * Pode gerenciar = é admin_municipal do município,
     * OU é gestor_conselho com vínculo explícito a este conselho,
     * OU é PRESIDENTE ou SECRETARIO ativo neste conselho específico.
     */
    private function podeGerenciar(User $user, Conselho $conselho): bool
    {
        if ($user->hasRole('admin_municipal')) {
            return $user->municipio_id === $conselho->municipio_id;
        }

        if ($user->hasRole('gestor_conselho')) {
            return $user->gerenciaConselho($conselho->id);
        }

        return $this->isGestorAtivo($user, $conselho);
    }

    /**
     * O usuário é PRESIDENTE ou SECRETARIO ativo deste conselho?
     */
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

    /**
     * O usuário é PRESIDENTE ativo deste conselho?
     * (necessário para poder promover outros membros)
     */
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

    /**
     * O usuário é membro ativo (qualquer tipo) deste conselho?
     */
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
