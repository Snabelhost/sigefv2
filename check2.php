<?php
// Minimalista - só carregar vendor autoload
require_once 'vendor/autoload.php';

echo "Livewire EventBus: " . (class_exists('Livewire\EventBus') ? 'YES' : 'NO') . "\n";

// Testar se podemos criar a app com cache limpo
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

try {
    $kernel->bootstrap();
    echo "Bootstrap completed.\n";
    
    // Verificar providers carregados
    $loadedProviders = $app->getLoadedProviders();
    
    echo "\nFilament/Livewire Providers:\n";
    foreach ($loadedProviders as $provider => $loaded) {
        if (str_contains($provider, 'Filament') || str_contains($provider, 'Livewire')) {
            echo " - $provider: " . ($loaded ? 'loaded' : 'not loaded') . "\n";
        }
    }
    
    echo "\nFilament binding: " . ($app->bound('filament') ? 'YES' : 'NO') . "\n";
    
    if ($app->bound('filament')) {
        $filament = $app->make('filament');
        echo "FilamentManager: " . get_class($filament) . "\n";
    }
    
} catch (Exception $e) {
    echo "Error during bootstrap: " . $e->getMessage() . "\n";
    echo "At: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
