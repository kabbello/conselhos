<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\Comissoes\Models\Comissao;
use Modules\Comissoes\Policies\ComissaoPolicy;
use Modules\Composicao\Models\Composicao;
use Modules\Composicao\Policies\ComposicaoPolicy;
use Modules\Conselhos\Models\Conselho;
use Modules\Conselhos\Policies\ConselhoPolicy;
use Modules\Documentos\Models\Documento;
use Modules\Documentos\Policies\DocumentoPolicy;
use Modules\Resolucoes\Models\AtoNormativo;
use Modules\Resolucoes\Models\Processo;
use Modules\Resolucoes\Policies\AtoNormativoPolicy;
use Modules\Resolucoes\Policies\ProcessoPolicy;
use Modules\Reunioes\Models\Reuniao;
use Modules\Reunioes\Policies\ReuniaoPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Composicao::class   => ComposicaoPolicy::class,
        Conselho::class     => ConselhoPolicy::class,
        Comissao::class     => ComissaoPolicy::class,
        Processo::class     => ProcessoPolicy::class,
        AtoNormativo::class => AtoNormativoPolicy::class,
        Reuniao::class      => ReuniaoPolicy::class,
        Documento::class    => DocumentoPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // super_admin bypassa todas as gates/policies
        Gate::before(function (\App\Models\User $user, string $ability) {
            if ($user->hasRole('super_admin')) {
                return true;
            }
        });
    }
}
