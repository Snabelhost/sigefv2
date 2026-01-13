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
use Illuminate\Support\Facades\Blade;

class EscolaPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('escola')
            ->path('escola')
            ->login(false) // Desabilitar login do painel - usar /login unificado
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->globalSearchDebounce(500)
            ->colors([
                'primary' => [
                    50 => '236, 239, 245',
                    100 => '200, 210, 230',
                    200 => '150, 170, 200',
                    300 => '100, 130, 170',
                    400 => '50, 90, 140',
                    500 => '4, 24, 66',
                    600 => '4, 24, 66',
                    700 => '3, 20, 55',
                    800 => '2, 15, 45',
                    900 => '2, 12, 35',
                    950 => '1, 8, 25',
                ],
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => '
                    <link rel="stylesheet" href="/css/sigef-theme.css">
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
            ->tenant(\App\Models\Institution::class)
            ->discoverResources(in: app_path('Filament/Escola/Resources'), for: 'App\\Filament\\Escola\\Resources')
            ->discoverPages(in: app_path('Filament/Escola/Pages'), for: 'App\\Filament\\Escola\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Escola/Widgets'), for: 'App\\Filament\\Escola\\Widgets')
            ->widgets([
                \App\Filament\Escola\Widgets\EscolaStatsOverview::class,
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
                // \\BezhanSalleh\\FilamentShield\\FilamentShieldPlugin::make(), // Temporariamente desabilitado para debug
            ])
            ->authMiddleware([
                Authenticate::class, // Middleware customizado que redireciona para /login
                \App\Http\Middleware\RefreshUserPermissions::class, // Atualiza permissões a cada request
            ]);
    }
}

