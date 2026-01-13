<?php
/**
 * Script para mudar ícones de outline (heroicon-o-) para solid (heroicon-s-)
 * Ícones solid têm aparência mais moderna e preenchida
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
        
        // Substituir heroicon-o- por heroicon-s- (solid icons)
        $content = str_replace('heroicon-o-', 'heroicon-s-', $content);
        
        if ($content !== $originalContent) {
            file_put_contents($file->getPathname(), $content);
            $changes[] = $file->getPathname();
            echo "Corrigido: " . basename($file->getPathname()) . "\n";
        }
    }
}

echo "\n=== Resumo ===\n";
echo "Total de arquivos atualizados: " . count($changes) . "\n";
