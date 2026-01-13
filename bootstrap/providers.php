<?php

return [
    App\Providers\AppServiceProvider::class,
    \Livewire\LivewireServiceProvider::class,
    \Filament\Support\SupportServiceProvider::class,
    \Filament\FilamentServiceProvider::class,
    \Filament\Notifications\NotificationsServiceProvider::class,
    \Filament\Widgets\WidgetsServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    App\Providers\Filament\ComandoPanelProvider::class,
    App\Providers\Filament\DpqPanelProvider::class,
    App\Providers\Filament\EscolaPanelProvider::class,
    App\Providers\FilamentTableServiceProvider::class,
    \Spatie\Permission\PermissionServiceProvider::class,
    \BezhanSalleh\FilamentShield\FilamentShieldServiceProvider::class,
    \Barryvdh\DomPDF\ServiceProvider::class,
];
