<?php

namespace App\Filament\Resources\Shield;

use BezhanSalleh\FilamentShield\Resources\Roles\RoleResource as BaseRoleResource;
use App\Filament\Resources\Shield\RoleResource\Pages;

class RoleResource extends BaseRoleResource
{
    /**
     * Sobrescreve as páginas para usar nossa página de edição personalizada
     * que força a limpeza do cache de permissões.
     */
    public static function getPages(): array
    {
        return [
            'index' => \BezhanSalleh\FilamentShield\Resources\Roles\Pages\ListRoles::route('/'),
            'create' => \BezhanSalleh\FilamentShield\Resources\Roles\Pages\CreateRole::route('/create'),
            'view' => \BezhanSalleh\FilamentShield\Resources\Roles\Pages\ViewRole::route('/{record}'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
