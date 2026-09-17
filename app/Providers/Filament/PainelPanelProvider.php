<?php

namespace App\Providers\Filament;

use App\Models\Municipio;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
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
    public function register(): void
    {
        parent::register();

        // A09: banner de aviso inequívoco durante sessão de impersonation.
        // O package lab404/laravel-impersonate armazena o ID do impersonador na sessão.
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_START,
            fn (): string => $this->impersonationBanner(),
        );
    }

    private function impersonationBanner(): string
    {
        // A chave de sessão usada pelo lab404/laravel-impersonate
        if (! session()->has('impersonated_by')) {
            return '';
        }

        $impersonatorId   = session('impersonated_by');
        $impersonatorName = \App\Models\User::find($impersonatorId)?->name ?? 'administrador';

        return <<<HTML
        <div style="
            position: fixed; top: 0; left: 0; right: 0; z-index: 9999;
            background: #b45309; color: #fff;
            padding: 8px 16px; text-align: center; font-weight: 600; font-size: 0.85rem;
        ">
            ⚠ Você está em modo de impersonation — sessão de <em>{$impersonatorName}</em>.
            Todas as ações são registradas.
            <a href="/filament-impersonate/leave"
               style="margin-left: 16px; color: #fde68a; text-decoration: underline;">
               Encerrar sessão
            </a>
        </div>
        HTML;
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('painel')
            ->path('painel')
            ->login(\App\Filament\Painel\Pages\Login::class)
            ->passwordReset()
            ->profile(\App\Filament\Painel\Pages\PerfilConselheiro::class)
            ->colors([
                'primary' => Color::Blue,
            ])
            ->brandName('Sistema de Conselhos')
            ->renderHook(
                'panels::footer',
                fn () => '<div style="text-align:center;padding:8px 0;font-size:11px;color:#94a3b8;">v' . config('app.version') . ' &nbsp;·&nbsp; ' . config('app.name') . '</div>',
            )
            // Multi-tenancy: cada usuário pertence a um município
            ->tenant(Municipio::class, slugAttribute: 'slug')
            ->tenantRoutePrefix('municipio')
            ->discoverResources(
                in: base_path('Modules/Conselhos/Filament/Resources'),
                for: 'Modules\\Conselhos\\Filament\\Resources'
            )
            ->discoverResources(
                in: base_path('Modules/Composicao/Filament/Resources'),
                for: 'Modules\\Composicao\\Filament\\Resources'
            )
            ->discoverResources(
                in: base_path('Modules/Comissoes/Filament/Resources'),
                for: 'Modules\\Comissoes\\Filament\\Resources'
            )
            ->discoverResources(
                in: base_path('Modules/Resolucoes/Filament/Resources'),
                for: 'Modules\\Resolucoes\\Filament\\Resources'
            )
            ->discoverResources(
                in: base_path('Modules/Reunioes/Filament/Resources'),
                for: 'Modules\\Reunioes\\Filament\\Resources'
            )
            ->discoverResources(
                in: base_path('Modules/Documentos/Filament/Resources'),
                for: 'Modules\\Documentos\\Filament\\Resources'
            )
            ->discoverResources(
                in: app_path('Filament/Painel/Resources'),
                for: 'App\\Filament\\Painel\\Resources'
            )
            ->discoverResources(
                in: base_path('Modules/Auditoria/Filament/Resources'),
                for: 'Modules\\Auditoria\\Filament\\Resources'
            )
            ->discoverResources(
                in: base_path('Modules/LGPD/Filament/Resources'),
                for: 'Modules\\LGPD\\Filament\\Resources'
            )
            ->discoverPages(
                in: base_path('Modules/Painel/Pages'),
                for: 'Modules\\Painel\\Pages'
            )
            ->pages([
                Pages\Dashboard::class,
            ])
            ->navigationItems([
                NavigationItem::make('Central de Ajuda')
                    ->url('/ajuda', shouldOpenInNewTab: true)
                    ->icon('heroicon-o-question-mark-circle')
                    ->group('Suporte')
                    ->sort(99),
            ])
            ->discoverWidgets(
                in: app_path('Filament/Painel/Widgets'),
                for: 'App\\Filament\\Painel\\Widgets'
            )
            ->widgets([
                Widgets\AccountWidget::class,
                \App\Filament\Painel\Widgets\MunicipioStatsWidget::class,
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
                \App\Http\Middleware\ForcePasswordReset::class,
            ])
            ->authGuard('web');
    }
}
