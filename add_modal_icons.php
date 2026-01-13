<?php

/**
 * Script para adicionar ícones aos botões do modal
 */

$resourcesPath = __DIR__ . '/app/Filament/Resources';

$files = glob($resourcesPath . '/*.php');

foreach ($files as $file) {
    $content = file_get_contents($file);
    $modified = false;
    
    // Procurar pelo CreateAction e adicionar configurações de botões do modal
    if (strpos($content, 'CreateAction::make()') !== false) {
        
        // Se já tem ->icon('heroicon-o-plus') mas não tem modalSubmitAction(fn
        if (strpos($content, "->icon('heroicon-o-plus')") !== false && 
            strpos($content, '->modalSubmitAction(fn') === false) {
            
            // Adicionar configuração dos botões do modal com ícones
            $content = str_replace(
                "->icon('heroicon-o-plus')",
                "->icon('heroicon-o-plus')\n                    ->modalSubmitAction(fn (\Filament\Actions\StaticAction \$action) => \$action->icon('heroicon-o-check')->label('Criar'))\n                    ->modalCancelAction(fn (\Filament\Actions\StaticAction \$action) => \$action->icon('heroicon-o-x-mark')->label('Cancelar'))",
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
