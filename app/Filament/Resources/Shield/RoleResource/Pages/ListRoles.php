<?php

namespace App\Filament\Resources\Shield\RoleResource\Pages;

use Filament\Resources\Pages\ListRecords;
use BezhanSalleh\FilamentShield\Resources\Roles\RoleResource;
use Filament\Actions;

class ListRoles extends ListRecords
{
    protected static string $resource = \App\Filament\Resources\Shield\RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-o-plus')
                ->label('Criar Função'),
        ];
    }
}
