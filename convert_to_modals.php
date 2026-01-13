<?php

/**
 * Script para converter Resources do Filament para usar Modais
 * Execute com: php convert_to_modals.php
 */

$resourcesPath = __DIR__ . '/app/Filament/Resources';

// Lista de Resources para converter (exceto RoleResource)
$resources = [
    'AcademicYearResource.php',
    'CandidateResource.php',
    'CourseMapResource.php',
    'CoursePhaseResource.php',
    'CoursePlanResource.php',
    'CourseResource.php',
    'EquipmentAssignmentResource.php',
    'EvaluationResource.php',
    'InstitutionResource.php',
    'InstitutionTypeResource.php',
    'ProvenanceResource.php',
    'RankResource.php',
    'RecruitmentTypeResource.php',
    'SelectionTestResource.php',
    'StudentClassResource.php',
    'StudentLeaveResource.php',
    'StudentResource.php',
    'SubjectResource.php',
    'TrainerResource.php',
    'UserResource.php',
];

foreach ($resources as $resource) {
    $filePath = $resourcesPath . '/' . $resource;
    
    if (!file_exists($filePath)) {
        echo "Arquivo não encontrado: $filePath\n";
        continue;
    }
    
    $content = file_get_contents($filePath);
    
    // Verificar se já tem CreateAction::make() na tabela
    if (strpos($content, 'Tables\Actions\CreateAction::make()') !== false) {
        echo "Já convertido: $resource\n";
        continue;
    }
    
    // Substituir o headerActions para incluir CreateAction com modal
    if (strpos($content, '->headerActions([') !== false) {
        // Já tem headerActions, adicionar CreateAction
        $content = preg_replace(
            '/->headerActions\(\[\s*\n/',
            "->headerActions([\n                Tables\\Actions\\CreateAction::make(),\n",
            $content
        );
    } else {
        // Não tem headerActions, adicionar após filters ou antes de actions
        $content = preg_replace(
            '/(->filters\(\[\s*[^]]*\]\))/s',
            "$1\n            ->headerActions([\n                Tables\\Actions\\CreateAction::make(),\n            ])",
            $content
        );
    }
    
    // Substituir EditAction para usar modal
    $content = str_replace(
        '\\Filament\\Actions\\EditAction::make()',
        'Tables\\Actions\\EditAction::make()',
        $content
    );
    
    // Remover as páginas create e edit do getPages()
    $content = preg_replace(
        "/'create' => Pages.*?route\('\/create'\),\s*/",
        '',
        $content
    );
    $content = preg_replace(
        "/'edit' => Pages.*?route\('\/{record}\/edit'\),\s*/",
        '',
        $content
    );
    
    file_put_contents($filePath, $content);
    echo "Convertido: $resource\n";
}

echo "\nConversão concluída!\n";
