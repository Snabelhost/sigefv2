<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$loadedProviders = $app->getLoadedProviders();

echo "=== ALL Loaded Providers ===\n";
foreach ($loadedProviders as $provider => $loaded) {
    echo " - $provider\n";
}

echo "\n=== Check Package Manifest ===\n";
$manifest = require 'bootstrap/cache/packages.php';
echo "Packages in manifest: " . count($manifest) . "\n";

echo "\n=== Checking if Filament providers are in manifest but not loaded ===\n";
$filamentPackages = ['filament/filament', 'filament/support', 'livewire/livewire'];
foreach ($filamentPackages as $pkg) {
    if (isset($manifest[$pkg]['providers'])) {
        foreach ($manifest[$pkg]['providers'] as $provider) {
            $isLoaded = isset($loadedProviders[$provider]);
            echo " - $provider: " . ($isLoaded ? 'LOADED' : 'NOT LOADED') . "\n";
        }
    }
}
