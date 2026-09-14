<?php

namespace Modules\Conselhos\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Conselhos\Models\Conselho;
use Modules\Conselhos\Policies\ConselhoPolicy;

class ConselhoServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(Conselho::class, ConselhoPolicy::class);
    }
}
