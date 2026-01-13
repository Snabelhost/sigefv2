<?php
/**
 * Script para corrigir métodos deprecados do Filament 4
 */

$resourcesDir = __DIR__ . '/app/Filament';

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($resourcesDir)
);

$changes = [];

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        $originalContent = $content;
        
        // Substituir circleAppearance() por avatar() - Filament 4
        $content = preg_replace(
            '/->circleAppearance\(\)/',
            '->avatar()',
            $content
        );
        
        // Remover circular() de ImageColumn se causar problemas
        // Na verdade, circular() ainda existe em ImageColumn, não precisa mudar
        
        // Substituir BadgeColumn por TextColumn com badge()
        $content = preg_replace(
            '/Tables\\\\Columns\\\\BadgeColumn::make/',
            'Tables\\Columns\\TextColumn::make',
            $content
        );
        
        // Adicionar ->badge() após o ::make para campos que eram BadgeColumn
        // Isso é mais complexo, vamos fazer manualmente se necessário
        
        if ($content !== $originalContent) {
            file_put_contents($file->getPathname(), $content);
            $changes[] = $file->getPathname();
            echo "Corrigido: " . $file->getPathname() . "\n";
        }
    }
}

echo "\n=== Resumo ===\n";
echo "Total de arquivos corrigidos: " . count($changes) . "\n";
