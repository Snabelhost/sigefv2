<?php

/**
 * Script para corrigir TODOS os namespaces das Actions
 */

$resourcesPath = __DIR__ . '/app/Filament/Resources';

$files = glob($resourcesPath . '/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Corrigir Tables\Actions\EditAction para \Filament\Actions\EditAction
    $content = str_replace(
        'Tables\Actions\EditAction::make()',
        '\Filament\Actions\EditAction::make()',
        $content
    );
    
    // Corrigir Tables\Actions\DeleteAction para \Filament\Actions\DeleteAction
    $content = str_replace(
        'Tables\Actions\DeleteAction::make()',
        '\Filament\Actions\DeleteAction::make()',
        $content
    );
    
    file_put_contents($file, $content);
    echo "Corrigido: " . basename($file) . "\n";
}

echo "\nCorreção concluída!\n";
