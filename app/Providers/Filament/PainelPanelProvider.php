<?php

namespace App\Providers\Filament;

use App\Models\Municipio;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * Painel /painel — acesso para admin_municipal, gestor_conselho, conselheiro, operador, etc.
 * Multi-tenant: isolado por Município
 */
class PainelPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('painel')
            ->path('painel')
            ->login()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->brandName('Sistema de Conselhos')
            // Multi-tenancy: cada usuário pertence a um município
            ->tenant(Municipio::class, slugAttribute: 'slug')
            ->tenantRoutePrefix('municipio')
            ->discoverResources(
                in: app_path('Modules'),
                for: 'App\\Modules'
            )
            ->discoverPages(
                in: app_path('Filament/Painel/Pages'),
                for: 'App\\Filament\\Painel\\Pages'
            )
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(
                in: app_path('Filament/Painel/Widgets'),
                for: 'App\\Filament\\Painel\\Widgets'
            )
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->authGuard('web');
    }
}
