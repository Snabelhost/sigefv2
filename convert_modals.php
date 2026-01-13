<?php

/**
 * Script para converter Resources do Filament para usar Modais
 * Execute com: php artisan tinker < convert_modals.php
 */

$resourcesPath = __DIR__ . '/app/Filament/Resources';

// Lista de Resources para converter (exceto Shield/RoleResource)
$resources = [
    'ProvenanceResource',
    'RankResource',
    'RecruitmentTypeResource',
    'CoursePhaseResource',
    'SubjectResource',
    'CourseResource',
    'CoursePlanResource',
    'CourseMapResource',
    'SelectionTestResource',
    'StudentClassResource',
    'StudentLeaveResource',
    'EquipmentAssignmentResource',
    'CandidateResource',
    'StudentResource',
    'TrainerResource',
    'EvaluationResource',
    'InstitutionResource',
    'UserResource',
];

foreach ($resources as $resource) {
    $file = $resourcesPath . '/' . $resource . '.php';
    
    if (!file_exists($file)) {
        echo "Arquivo não encontrado: $resource\n";
        continue;
    }
    
    $content = file_get_contents($file);
    
    // Verificar se já foi convertido
    if (strpos($content, 'Tables\Actions\CreateAction::make()') !== false) {
        echo "Já convertido: $resource\n";
        continue;
    }
    
    // 1. Adicionar headerActions com CreateAction após filters
    $content = preg_replace(
        '/(->filters\(\[\s*[^]]*?\]\))/s',
        "$1\n            ->headerActions([\n                Tables\\Actions\\CreateAction::make(),\n            ])",
        $content
    );
    
    // 2. Substituir \Filament\Actions\EditAction por Tables\Actions\EditAction
    $content = str_replace(
        '\Filament\Actions\EditAction::make()',
        'Tables\Actions\EditAction::make()',
        $content
    );
    
    // 3. Adicionar DeleteAction se não existir
    if (strpos($content, 'Tables\Actions\DeleteAction::make()') === false) {
        $content = str_replace(
            'Tables\Actions\EditAction::make(),',
            "Tables\\Actions\\EditAction::make(),\n                Tables\\Actions\\DeleteAction::make(),",
            $content
        );
    }
    
    // 4. Remover páginas create e edit do getPages
    $content = preg_replace("/\s*'create' => Pages.*?route\('\/create'\),/", '', $content);
    $content = preg_replace("/\s*'edit' => Pages.*?route\('\/{record}\/edit'\),/", '', $content);
    
    file_put_contents($file, $content);
    echo "Convertido: $resource\n";
}

echo "\nConversão concluída!\n";
echo "Agora execute: php artisan optimize:clear\n";
