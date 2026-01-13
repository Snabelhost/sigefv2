<?php

/**
 * Script para adicionar ícone Plus ao CreateAction em todos os Resources
 */

$resourcesPath = __DIR__ . '/app/Filament/Resources';

$files = glob($resourcesPath . '/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Verificar se já tem o ícone
    if (strpos($content, "->icon('heroicon-") !== false && strpos($content, 'CreateAction::make()') !== false) {
        echo "Já tem ícone: " . basename($file) . "\n";
        continue;
    }
    
    // Adicionar ícone ao CreateAction
    $content = str_replace(
        '\Filament\Actions\CreateAction::make()',
        '\Filament\Actions\CreateAction::make()->icon(\'heroicon-o-plus\')',
        $content
    );
    
    file_put_contents($file, $content);
    echo "Atualizado: " . basename($file) . "\n";
}

echo "\nAtualização concluída!\n";
