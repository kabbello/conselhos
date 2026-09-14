<?php

namespace Modules\Composicao\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Composicao\Models\Composicao;
use Modules\Composicao\Policies\ComposicaoPolicy;

class ComposicaoServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(Composicao::class, ComposicaoPolicy::class);
    }
}
