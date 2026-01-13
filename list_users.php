<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$users = App\Models\User::with('roles')->get();

echo "=== UTILIZADORES DO SISTEMA ===\n\n";
foreach($users as $u) {
    echo "Email: " . $u->email . "\n";
    echo "Nome: " . $u->name . "\n";
    echo "Roles: " . $u->roles->pluck('name')->implode(', ') . "\n";
    echo "Institution ID: " . ($u->institution_id ?? 'N/A') . "\n";
    echo "---\n";
}
