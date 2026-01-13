<?php

namespace App\Providers;

use App\Http\Responses\LogoutResponse;
use App\Listeners\PermissionEventSubscriber;
use App\Observers\RoleObserver;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse as LogoutResponseContract;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\HtmlString;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar LogoutResponse personalizado para redirecionar corretamente ao sair
        $this->app->bind(LogoutResponseContract::class, LogoutResponse::class);
        
        $helpers = [
            'vendor/livewire/livewire/src/helpers.php',
            'vendor/blade-ui-kit/blade-icons/src/helpers.php',
            'vendor/filament/filament/src/global_helpers.php',
            'vendor/filament/filament/src/helpers.php',
            'vendor/filament/forms/src/helpers.php',
            'vendor/filament/support/src/helpers.php',
            'vendor/spatie/laravel-permission/src/helpers.php',
        ];

        foreach ($helpers as $helper) {
            if (file_exists($file = base_path($helper))) {
                require_once $file;
            }
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar Observer para limpar cache de permissões quando roles são atualizadas
        Role::observe(RoleObserver::class);
        
        // Registrar Event Subscriber para limpar cache quando permissões são alteradas
        Event::subscribe(PermissionEventSubscriber::class);
        
        // Registrar CSS personalizado do SIGEF em todos os painéis Filament (no HEAD)
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn (): HtmlString => new HtmlString('<link rel="stylesheet" href="/css/sigef-theme.css">')
        );
    }
}


