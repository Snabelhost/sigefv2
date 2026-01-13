<?php
/**
 * Script para corrigir TODAS as Actions do Filament 4
 * No Filament 4, todas as Actions estão em Filament\Actions
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
        
        // Substituir Tables\Actions\BulkActionGroup por \Filament\Actions\BulkActionGroup
        $content = str_replace(
            'Tables\\Actions\\BulkActionGroup',
            '\\Filament\\Actions\\BulkActionGroup',
            $content
        );
        
        // Substituir Tables\Actions\DeleteBulkAction por \Filament\Actions\DeleteBulkAction
        $content = str_replace(
            'Tables\\Actions\\DeleteBulkAction',
            '\\Filament\\Actions\\DeleteBulkAction',
            $content
        );
        
        // Substituir Tables\Actions\ViewAction por \Filament\Actions\ViewAction
        $content = str_replace(
            'Tables\\Actions\\ViewAction',
            '\\Filament\\Actions\\ViewAction',
            $content
        );
        
        // Substituir Tables\Actions\EditAction por \Filament\Actions\EditAction
        $content = str_replace(
            'Tables\\Actions\\EditAction',
            '\\Filament\\Actions\\EditAction',
            $content
        );
        
        // Substituir Tables\Actions\DeleteAction por \Filament\Actions\DeleteAction
        $content = str_replace(
            'Tables\\Actions\\DeleteAction',
            '\\Filament\\Actions\\DeleteAction',
            $content
        );
        
        // Substituir Tables\Actions\CreateAction por \Filament\Actions\CreateAction
        $content = str_replace(
            'Tables\\Actions\\CreateAction',
            '\\Filament\\Actions\\CreateAction',
            $content
        );
        
        // Substituir Tables\Actions\ForceDeleteAction por \Filament\Actions\ForceDeleteAction
        $content = str_replace(
            'Tables\\Actions\\ForceDeleteAction',
            '\\Filament\\Actions\\ForceDeleteAction',
            $content
        );
        
        // Substituir Tables\Actions\RestoreAction por \Filament\Actions\RestoreAction
        $content = str_replace(
            'Tables\\Actions\\RestoreAction',
            '\\Filament\\Actions\\RestoreAction',
            $content
        );
        
        // Substituir Tables\Actions\ForceDeleteBulkAction por \Filament\Actions\ForceDeleteBulkAction
        $content = str_replace(
            'Tables\\Actions\\ForceDeleteBulkAction',
            '\\Filament\\Actions\\ForceDeleteBulkAction',
            $content
        );
        
        // Substituir Tables\Actions\RestoreBulkAction por \Filament\Actions\RestoreBulkAction  
        $content = str_replace(
            'Tables\\Actions\\RestoreBulkAction',
            '\\Filament\\Actions\\RestoreBulkAction',
            $content
        );
        
        if ($content !== $originalContent) {
            file_put_contents($file->getPathname(), $content);
            $changes[] = $file->getPathname();
            echo "Corrigido: " . $file->getPathname() . "\n";
        }
    }
}

echo "\n=== Resumo ===\n";
echo "Total de arquivos corrigidos: " . count($changes) . "\n";
