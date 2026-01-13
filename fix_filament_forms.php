<?php
/**
 * Script para corrigir os componentes de Forms do Filament 4
 * No Filament 4, os componentes de layout foram movidos para Filament\Schemas\Components
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
        
        // Componentes de layout que foram movidos para Schemas\Components
        $layoutComponents = [
            'Section',
            'Grid', 
            'Fieldset',
            'Tabs',
            'Tabs\\Tab',
            'Wizard',
            'Wizard\\Step',
            'Card',
            'Group',
            'Split',
            'View',
        ];
        
        foreach ($layoutComponents as $component) {
            // Substituir Forms\Components\$component por \Filament\Schemas\Components\$component
            $pattern = '/Forms\\\\Components\\\\' . preg_quote($component, '/') . '([^a-zA-Z])/';
            $replacement = '\\Filament\\Schemas\\Components\\' . $component . '$1';
            $content = preg_replace($pattern, $replacement, $content);
        }
        
        // Também corrigir Forms\Components\<Component>::make para os componentes de layout
        foreach ($layoutComponents as $component) {
            $content = str_replace(
                'Forms\\Components\\' . $component . '::make',
                '\\Filament\\Schemas\\Components\\' . $component . '::make',
                $content
            );
        }
        
        if ($content !== $originalContent) {
            file_put_contents($file->getPathname(), $content);
            $changes[] = $file->getPathname();
            echo "Corrigido: " . $file->getPathname() . "\n";
        }
    }
}

echo "\n=== Resumo ===\n";
echo "Total de arquivos corrigidos: " . count($changes) . "\n";
