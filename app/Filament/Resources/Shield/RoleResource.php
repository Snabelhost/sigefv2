<?php

namespace App\Filament\Resources\Shield;

use BezhanSalleh\FilamentShield\Resources\Roles\RoleResource as BaseRoleResource;
use App\Filament\Resources\Shield\RoleResource\Pages;
use Spatie\Permission\Models\Role;

class RoleResource extends BaseRoleResource
{
    /**
     * Retorna o badge com o número de funções
     */
    public static function getNavigationBadge(): ?string
    {
        return (string) Role::count();
    }

    /**
     * Cor do badge
     */
    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

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
