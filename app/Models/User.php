<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Composicao\Models\Conselheiro;
use Modules\Conselhos\Models\Conselho;
use Modules\Municipios\Models\Municipio;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasTenants
{
    use HasFactory, HasRoles, LogsActivity, Notifiable;

    protected $fillable = [
        'municipio_id',
        'name',
        'email',
        'password',
        'must_reset_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'   => 'datetime',
            'password'            => 'hashed',
            'must_reset_password' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['name', 'email', 'municipio_id']);
    }

    // ---------- Filament: controle de acesso aos painéis ----------

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin'  => $this->hasRole('super_admin'),
            // No painel municipal: qualquer usuário vinculado a um município pode entrar.
            // O acesso granular por conselho é controlado pelas Policies, não aqui.
            'painel' => $this->municipio_id !== null,
            default  => false,
        };
    }

    // ---------- Impersonação ----------

    /** Apenas admin_municipal e super_admin podem impersonar outros. */
    public function canImpersonate(): bool
    {
        return $this->hasAnyRole(['super_admin', 'admin_municipal']);
    }

    /** super_admin nunca pode ser impersonado. */
    public function canBeImpersonated(): bool
    {
        return ! $this->hasRole('super_admin');
    }

    // ---------- Filament Multi-tenancy ----------

    /**
     * Municípios aos quais este usuário tem acesso.
     * super_admin vê todos; demais veem apenas o próprio.
     */
    public function getTenants(Panel $panel): Collection
    {
        if ($this->hasRole('super_admin')) {
            return Municipio::where('ativo', true)->get();
        }

        return Municipio::where('id', $this->municipio_id)  // @phpstan-ignore-line
            ->where('ativo', true)
            ->get();
    }

    public function canAccessTenant(Model $tenant): bool
    {
        if ($this->hasRole('super_admin')) {
            return true;
        }

        return $this->municipio_id === $tenant->id;
    }

    // ---------- Relacionamentos ----------

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    public function conselheiro(): HasOne
    {
        return $this->hasOne(Conselheiro::class);
    }

    /**
     * Conselhos sobre os quais este usuário tem papel de gestor_conselho.
     * Apenas vínculos ativos (revogado_em nulo) são considerados.
     */
    public function conselhosSobGestao(): BelongsToMany
    {
        return $this->belongsToMany(
            Conselho::class,
            'user_conselho_gestores',
            'user_id',
            'conselho_id',
        )
        ->withPivot(['atribuido_por', 'atribuido_em', 'revogado_em'])
        ->wherePivotNull('revogado_em');
    }

    /**
     * Verifica se o usuário é gestor ativo do conselho informado.
     * Usado pelas Policies quando o usuário tem o papel gestor_conselho.
     */
    public function gerenciaConselho(int $conselhoId): bool
    {
        return $this->conselhosSobGestao()
            ->where('conselho_id', $conselhoId)
            ->exists();
    }

}
