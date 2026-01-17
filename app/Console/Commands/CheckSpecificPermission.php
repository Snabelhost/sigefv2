<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CheckSpecificPermission extends Command
{
    protected $signature = 'permissions:check-specific {role} {permission}';
    protected $description = 'Verifica se uma role tem uma permissão específica';

    public function handle()
    {
        $roleName = $this->argument('role');
        $permissionName = $this->argument('permission');
        
        $role = Role::where('name', $roleName)->first();
        
        if (!$role) {
            $this->error("Role {$roleName} não encontrada!");
            return 1;
        }
        
        $permission = Permission::where('name', $permissionName)->first();
        
        if (!$permission) {
            $this->warn("Permissão {$permissionName} NÃO existe na base de dados!");
            
            // Listar permissões similares
            $similar = Permission::where('name', 'like', '%' . explode(':', $permissionName)[1] . '%')->pluck('name');
            if ($similar->count() > 0) {
                $this->info("Permissões similares encontradas:");
                foreach ($similar as $p) {
                    $this->line("  - {$p}");
                }
            }
            return 1;
        }
        
        $has = $role->hasPermissionTo($permissionName);
        
        if ($has) {
            $this->info("✅ Role '{$roleName}' TEM a permissão '{$permissionName}'");
        } else {
            $this->warn("❌ Role '{$roleName}' NÃO TEM a permissão '{$permissionName}'");
        }
        
        return 0;
    }
}
