<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
            'painel' => $this->hasAnyRole(['admin_municipal', 'gestor_conselho', 'conselheiro', 'operador', 'encarregado_dados', 'auditor']),
            default  => false,
        };
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

        return Municipio::where('id', $this->municipio_id)
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
}
