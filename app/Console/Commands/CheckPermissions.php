<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CheckPermissions extends Command
{
    protected $signature = 'permissions:check {role=super_admin}';
    protected $description = 'Verifica permissões de uma role';

    public function handle()
    {
        $roleName = $this->argument('role');
        $role = Role::where('name', $roleName)->first();
        
        if (!$role) {
            $this->error("Role {$roleName} não encontrada!");
            return 1;
        }
        
        $this->info("Role: {$role->name}");
        $this->info("Total permissões: " . $role->permissions()->count());
        
        // Verificar permissões específicas
        $permissions = [
            'ViewAny:Audit',
            'View:Audit',
            'ViewAny:ActivityLog',
            'View:ActivityLog',
        ];
        
        $this->newLine();
        $this->table(
            ['Permissão', 'Status'],
            collect($permissions)->map(fn($p) => [
                $p,
                $role->hasPermissionTo($p) ? '✅ SIM' : '❌ NÃO'
            ])->toArray()
        );
        
        return 0;
    }
}
