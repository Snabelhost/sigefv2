<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = \App\Models\User::where('email', 'admin@sigef.com')->first();

if ($user) {
    echo "User: " . $user->email . PHP_EOL;
    echo "Roles: " . implode(', ', $user->getRoleNames()->toArray()) . PHP_EOL;
    
    // Verificar permissões específicas
    $permissions = [
        'view_agent',
        'view_any_agent',
        'view_candidate',
        'view_any_candidate',
        'view_student',
        'view_any_student',
    ];
    
    echo "\n=== Permissions ===\n";
    foreach ($permissions as $perm) {
        echo "$perm: " . ($user->can($perm) ? 'YES' : 'NO') . PHP_EOL;
    }
} else {
    echo "User not found!\n";
}
