<?php

namespace App\Filament\Resources\Shield\RoleResource\Pages;

use BezhanSalleh\FilamentShield\Resources\Roles\Pages\EditRole as BaseEditRole;
use Spatie\Permission\PermissionRegistrar;

class EditRole extends BaseEditRole
{
    /**
     * Após salvar, limpa o cache de permissões para todos os usuários.
     */
    protected function afterSave(): void
    {
        // Executa o afterSave original que sincroniza as permissões
        parent::afterSave();
        
        // Força a limpeza completa do cache de permissões
        $this->forcePermissionCacheRefresh();
    }

    /**
     * Limpa todos os caches relacionados a permissões.
     */
    protected function forcePermissionCacheRefresh(): void
    {
        // 1. Limpa o cache do Spatie Permission
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        
        // 2. Limpa o cache geral da aplicação relacionado a permissões
        cache()->forget('spatie.permission.cache');
        
        // 3. Limpa caches do dashboard
        cache()->forget('dashboard_stats');
        cache()->forget('candidates_by_province_chart');
        
        // 4. Log para debug
        logger()->info('Permission cache forcefully cleared after role edit', [
            'role_id' => $this->record->id,
            'role_name' => $this->record->name,
            'permissions_count' => $this->record->permissions->count(),
        ]);
    }
}
