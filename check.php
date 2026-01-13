<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Checking Providers ===\n";
$providers = $app->getLoadedProviders();
$filamentProviders = array_filter($providers, fn($v, $k) => str_contains($k, 'Filament') || str_contains($k, 'Livewire'), ARRAY_FILTER_USE_BOTH);

echo "Loaded Filament/Livewire Providers:\n";
foreach ($filamentProviders as $provider => $loaded) {
    echo " - $provider: " . ($loaded ? 'loaded' : 'not loaded') . "\n";
}

echo "\n=== Checking Bindings ===\n";
echo "filament binding exists: " . ($app->bound('filament') ? 'YES' : 'NO') . "\n";

echo "\n=== Checking Panels ===\n";
try {
    $registry = $app->make(\Filament\PanelRegistry::class);
    $panels = $registry->all();
    echo "Panels registered: " . count($panels) . "\n";
    foreach ($panels as $id => $panel) {
        echo " - Panel: " . $id . " => path: " . $panel->getPath() . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Checking Routes ===\n";
$routes = app('router')->getRoutes();
$adminRoutes = 0;
foreach ($routes as $route) {
    if (str_starts_with($route->uri(), 'admin')) {
        $adminRoutes++;
    }
}
echo "Admin routes registered: $adminRoutes\n";
