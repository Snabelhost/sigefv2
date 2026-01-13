<?php

/**
 * Script para adicionar ícones aos botões das Actions (Create, Edit, Delete)
 */

$resourcesPath = __DIR__ . '/app/Filament/Resources';

$files = glob($resourcesPath . '/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    $modified = false;
    
    // Adicionar ícone ao CreateAction se ainda não tiver createAnother
    if (strpos($content, 'CreateAction::make()->icon(\'heroicon-o-plus\')') !== false) {
        // Verificar se já tem createAnother configurado
        if (strpos($content, '->createAnother(') === false) {
            // Adicionar configuração completa
            $content = str_replace(
                'CreateAction::make()->icon(\'heroicon-o-plus\')',
                "CreateAction::make()\n                    ->icon('heroicon-o-plus')\n                    ->createAnother(true)\n                    ->successNotificationTitle('Registo criado com sucesso!')",
                $content
            );
            $modified = true;
        }
    }
    
    // Adicionar ícone ao EditAction se não tiver
    if (strpos($content, 'EditAction::make()') !== false && strpos($content, "EditAction::make()->icon") === false) {
        $content = str_replace(
            '\\Filament\\Actions\\EditAction::make()',
            "\\Filament\\Actions\\EditAction::make()->icon('heroicon-o-pencil-square')",
            $content
        );
        $modified = true;
    }
    
    // Adicionar ícone ao DeleteAction se não tiver
    if (strpos($content, 'DeleteAction::make()') !== false && strpos($content, "DeleteAction::make()->icon") === false) {
        $content = str_replace(
            '\\Filament\\Actions\\DeleteAction::make()',
            "\\Filament\\Actions\\DeleteAction::make()->icon('heroicon-o-trash')",
            $content
        );
        $modified = true;
    }
    
    if ($modified) {
        file_put_contents($file, $content);
        echo "Atualizado: " . basename($file) . "\n";
    } else {
        echo "Sem alterações: " . basename($file) . "\n";
    }
}

echo "\nAtualização concluída!\n";
