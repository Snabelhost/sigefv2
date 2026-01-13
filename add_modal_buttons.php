<?php

/**
 * Script para adicionar ícones aos botões do modal (Create, CreateAnother, Cancel)
 */

$resourcesPath = __DIR__ . '/app/Filament/Resources';

$files = glob($resourcesPath . '/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    $modified = false;
    
    // Procurar pelo CreateAction e adicionar configurações de botões do modal
    if (strpos($content, 'CreateAction::make()') !== false) {
        
        // Se já tem ->icon('heroicon-o-plus') mas não tem modalSubmitAction
        if (strpos($content, "->icon('heroicon-o-plus')") !== false && 
            strpos($content, '->modalSubmitAction') === false) {
            
            // Adicionar configuração dos botões do modal
            $content = str_replace(
                "->icon('heroicon-o-plus')",
                "->icon('heroicon-o-plus')\n                    ->modalSubmitActionLabel('Criar')\n                    ->modalCancelActionLabel('Cancelar')",
                $content
            );
            $modified = true;
        }
    }
    
    if ($modified) {
        file_put_contents($file, $content);
        echo "Atualizado: " . basename($file) . "\n";
    } else {
        echo "Sem alterações: " . basename($file) . "\n";
    }
}

echo "\nAtualização concluída!\n";
echo "Nota: Os ícones nos botões do modal são controlados pelo Filament internamente.\n";
echo "Para adicionar ícones personalizados, seria necessário sobrescrever os métodos do modal.\n";
