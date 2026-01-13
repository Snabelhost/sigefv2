<?php

namespace App\Providers\Filament;

use App\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Blade;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(false) // Desabilitar login do painel - usar /login unificado
            ->brandLogo(fn () => view('filament.brand-logo'))
            ->brandLogoHeight('50px')
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->globalSearchDebounce(500)
            ->colors([
                'primary' => [
                    50 => '236, 239, 247',   // muito claro
                    100 => '200, 210, 235',
                    200 => '150, 170, 210',
                    300 => '100, 130, 175',
                    400 => '50, 80, 140',
                    500 => '4, 28, 79',      // #041c4f base
                    600 => '4, 28, 79',      // #041c4f
                    700 => '3, 22, 65',
                    800 => '2, 18, 50',
                    900 => '2, 14, 40',
                    950 => '1, 10, 30',
                ],
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => '
                    <link rel="stylesheet" href="/css/sigef-theme.css?v=' . time() . '">
                    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
                    <link rel="icon" type="image/png" href="/favicon.png">
                    <link rel="shortcut icon" href="/favicon.png">
                    <link rel="apple-touch-icon" href="/favicon.png">
                    <script src="/js/favicon-inject.js"></script>
                '
            )
            ->renderHook(
                PanelsRenderHook::CONTENT_START,
                fn () => view('filament.header')
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\StatsOverview::class,
                \App\Filament\Widgets\CandidatesByProvinceChart::class,
                \App\Filament\Widgets\CandidateStatusChart::class,
                \App\Filament\Widgets\StudentStatusChart::class,
                \App\Filament\Widgets\LatestNotifications::class,
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
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class, // Middleware customizado que redireciona para /login
                \App\Http\Middleware\RefreshUserPermissions::class, // Atualiza permissões a cada request
            ]);
    }
}

