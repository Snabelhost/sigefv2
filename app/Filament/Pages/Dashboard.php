<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-s-computer-desktop';
    
    protected static ?string $navigationLabel = 'Painel de Controle';
    
    protected static ?string $title = 'Painel de Controle';

    public static function getNavigationLabel(): string
    {
        return 'Painel de Controle';
    }
}
