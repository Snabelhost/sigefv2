<?php

namespace App\Filament\Resources\Shield\RoleResource\Pages;

use BezhanSalleh\FilamentShield\Resources\Roles\Pages\EditRole as BaseEditRole;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

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
     * Limpa todos os caches relacionados a permissões de forma agressiva.
     */
    protected function forcePermissionCacheRefresh(): void
    {
        // 1. Limpa o cache do Spatie Permission (método principal)
        $registrar = app(PermissionRegistrar::class);
        $registrar->forgetCachedPermissions();
        
        // 2. Força a reconfiguração do registrar
        $registrar->setPermissionsTeamId(null);
        
        // 3. Limpa o cache de todas as chaves relacionadas a permissões
        Cache::forget('spatie.permission.cache');
        
        // 4. Limpa caches relacionados a views
        Cache::flush(); // Limpa todo o cache (pode ser agressivo em produção)
        
        // 5. Limpa o cache de configuração do Filament
        try {
            Artisan::call('filament:cache-components');
        } catch (\Exception $e) {
            // Ignora se o comando falhar
        }
        
        // 6. Log para debug
        logger()->info('Permission cache forcefully cleared after role edit', [
            'role_id' => $this->record->id,
            'role_name' => $this->record->name,
            'permissions_count' => $this->record->permissions()->count(),
        ]);
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->icon('heroicon-o-check')
                ->label('Salvar'),
            $this->getCancelFormAction()
                ->icon('heroicon-o-x-mark')
                ->label('Cancelar')
                ->color('danger'),
        ];
    }
}
