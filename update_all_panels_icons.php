<?php

/**
 * Script para adicionar ícones aos botões do modal em todos os painéis
 */

$directories = [
    'app/Filament/Comando/Resources',
    'app/Filament/Dpq/Resources',
    'app/Filament/Escola/Resources',
];

$totalUpdated = 0;

foreach ($directories as $dir) {
    $fullPath = __DIR__ . '/' . $dir;
    
    if (!is_dir($fullPath)) {
        echo "Diretório não encontrado: $dir\n";
        continue;
    }
    
    $files = glob($fullPath . '/*Resource.php');
    
    foreach ($files as $file) {
        $content = file_get_contents($file);
        $filename = basename($file);
        $modified = false;
        
        // Se já tem modalSubmitAction, pular
        if (strpos($content, 'modalSubmitAction') !== false) {
            echo "Já atualizado: $dir/$filename\n";
            continue;
        }
        
        // Procurar CreateAction::make() e adicionar os métodos
        if (preg_match('/CreateAction::make\(\)\s*\n\s*->/', $content)) {
            
            // Adicionar icon se não existir
            if (strpos($content, "CreateAction::make()\n") !== false && 
                strpos($content, "->icon('heroicon-o-plus')") === false) {
                $content = preg_replace(
                    '/(CreateAction::make\(\))(\s*\n\s*->)/',
                    "$1\n                    ->icon('heroicon-o-plus')$2",
                    $content
                );
                $modified = true;
            }
            
            // Adicionar modalSubmitAction, modalCancelAction e createAnotherAction
            $pattern = '/(CreateAction::make\(\).*?)(->successNotificationTitle|->label|->modalHeading)/s';
            
            if (preg_match($pattern, $content, $matches)) {
                $replacement = $matches[1] . 
                    "->modalSubmitAction(fn (\\Filament\\Actions\\Action \$action) => \$action->icon('heroicon-o-check')->label('Criar'))\n" .
                    "                    ->modalCancelAction(fn (\\Filament\\Actions\\Action \$action) => \$action->icon('heroicon-o-x-mark')->label('Cancelar')->color('danger'))\n" .
                    "                    ->createAnotherAction(fn (\\Filament\\Actions\\Action \$action) => \$action->icon('heroicon-o-plus-circle')->label('Salvar e criar outro'))\n" .
                    "                    " . $matches[2];
                
                $content = preg_replace($pattern, $replacement, $content, 1);
                $modified = true;
            }
        }
        
        // Adicionar ícones ao EditAction e DeleteAction se não existirem
        if (strpos($content, "EditAction::make()") !== false && 
            strpos($content, "EditAction::make()->icon") === false) {
            $content = str_replace(
                "EditAction::make()",
                "EditAction::make()->icon('heroicon-o-pencil-square')",
                $content
            );
            $modified = true;
        }
        
        if (strpos($content, "DeleteAction::make()") !== false && 
            strpos($content, "DeleteAction::make()->icon") === false) {
            $content = str_replace(
                "DeleteAction::make()",
                "DeleteAction::make()->icon('heroicon-o-trash')",
                $content
            );
            $modified = true;
        }
        
        if ($modified) {
            file_put_contents($file, $content);
            echo "Atualizado: $dir/$filename\n";
            $totalUpdated++;
        } else {
            echo "Sem alterações: $dir/$filename\n";
        }
    }
}

echo "\n=== Total de arquivos atualizados: $totalUpdated ===\n";
