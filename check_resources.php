<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $resources = \Filament\Facades\Filament::getPanel('admin')->getResources();
    echo "Total Resources: " . count($resources) . PHP_EOL;
    
    echo "\n=== Resources com Formandos ===\n";
    foreach ($resources as $resource) {
        if (str_contains($resource, 'Agent') || str_contains($resource, 'Candidate') || str_contains($resource, 'Student')) {
            echo $resource . PHP_EOL;
            
            // Verificar navigationGroup
            if (class_exists($resource)) {
                $reflection = new ReflectionClass($resource);
                $props = $reflection->getDefaultProperties();
                if (isset($props['navigationGroup'])) {
                    echo "  -> navigationGroup: " . $props['navigationGroup'] . PHP_EOL;
                }
            }
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
