<?php

namespace App\Filament\Resources\Shield\RoleResource\Pages;

use BezhanSalleh\FilamentShield\Resources\Roles\Pages\CreateRole as BaseCreateRole;
use Filament\Actions\Action;

class CreateRole extends BaseCreateRole
{
    protected static string $resource = \App\Filament\Resources\Shield\RoleResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->icon('heroicon-o-check')
                ->label('Criar'),
            $this->getCreateAnotherFormAction()
                ->icon('heroicon-o-plus-circle')
                ->label('Salvar e criar outro'),
            $this->getCancelFormAction()
                ->icon('heroicon-o-x-mark')
                ->label('Cancelar')
                ->color('danger'),
        ];
    }
}
