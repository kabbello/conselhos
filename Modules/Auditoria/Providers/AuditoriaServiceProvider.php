<?php

namespace Modules\Auditoria\Providers;

use Illuminate\Support\ServiceProvider;

class AuditoriaServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(resource_path('views/auditoria'), 'auditoria');
    }
}
