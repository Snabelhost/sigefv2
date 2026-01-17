<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CheckUserRoles extends Command
{
    protected $signature = 'user:check-roles {email?}';
    protected $description = 'Verifica roles e permissões de um utilizador';

    public function handle()
    {
        $email = $this->argument('email') ?? 'admin@sigef.com';
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("Utilizador {$email} não encontrado!");
            return 1;
        }
        
        $this->info("Utilizador: {$user->name} ({$user->email})");
        $this->newLine();
        
        $this->info("Roles ({$user->roles->count()}):");
        foreach ($user->roles as $role) {
            $hasActivityLog = $role->hasPermissionTo('ViewAny:ActivityLog');
            $this->line("  - {$role->name}: {$role->permissions->count()} permissões | ActivityLog: " . ($hasActivityLog ? '✅' : '❌'));
        }
        
        $this->newLine();
        $canViewActivityLog = $user->can('ViewAny:ActivityLog');
        $this->info("Pode ver ActivityLog: " . ($canViewActivityLog ? '✅ SIM' : '❌ NÃO'));
        
        return 0;
    }
}
