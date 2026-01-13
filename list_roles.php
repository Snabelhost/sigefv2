<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$roles = Spatie\Permission\Models\Role::with('permissions')->get();

echo "=== ROLES DO SISTEMA ===\n\n";
foreach($roles as $r) {
    echo "Role: " . $r->name . "\n";
    echo "Guard: " . $r->guard_name . "\n";
    echo "Permissões: " . $r->permissions->pluck('name')->count() . " permissões\n";
    echo "---\n";
}
