<?php

/**
 * Script para reorganizar os grupos de navegação dos Resources
 */

$resourceGroups = [
    // GESTÃO DE ACESSO
    'UserResource.php' => ['group' => 'Gestão de Acesso', 'sort' => 1],
    
    // CURRÍCULO
    'CourseMapResource.php' => ['group' => 'Currículo', 'sort' => 1],
    'CoursePlanResource.php' => ['group' => 'Currículo', 'sort' => 2],
    'CoursePhaseResource.php' => ['group' => 'Currículo', 'sort' => 3],
    'CourseResource.php' => ['group' => 'Currículo', 'sort' => 4],
    
    // GESTÃO ESCOLAR
    'TrainerResource.php' => ['group' => 'Gestão Escolar', 'sort' => 1],
    'StudentResource.php' => ['group' => 'Gestão Escolar', 'sort' => 2],
    'CandidateResource.php' => ['group' => 'Gestão Escolar', 'sort' => 3, 'label' => 'Alistados', 'modelLabel' => 'Alistado', 'pluralLabel' => 'Alistados'],
    'EquipmentAssignmentResource.php' => ['group' => 'Gestão Escolar', 'sort' => 4],
    'SubjectResource.php' => ['group' => 'Gestão Escolar', 'sort' => 5],
    'StudentClassResource.php' => ['group' => 'Gestão Escolar', 'sort' => 6],
    'StudentLeaveResource.php' => ['group' => 'Gestão Escolar', 'sort' => 7],
    'EvaluationResource.php' => ['group' => 'Gestão Escolar', 'sort' => 8],
    'SelectionTestResource.php' => ['group' => 'Gestão Escolar', 'sort' => 9],
    
    // INSTITUIÇÕES
    'InstitutionResource.php' => ['group' => 'Instituições', 'sort' => 1],
    
    // CONFIGURAÇÕES
    'AcademicYearResource.php' => ['group' => 'Configurações', 'sort' => 1],
    'InstitutionTypeResource.php' => ['group' => 'Configurações', 'sort' => 2],
    'ProvenanceResource.php' => ['group' => 'Configurações', 'sort' => 3],
    'RankResource.php' => ['group' => 'Configurações', 'sort' => 4],
    'RecruitmentTypeResource.php' => ['group' => 'Configurações', 'sort' => 5],
];

$resourcesPath = __DIR__ . '/app/Filament/Resources/';
$updated = 0;

foreach ($resourceGroups as $filename => $config) {
    $filepath = $resourcesPath . $filename;
    
    if (!file_exists($filepath)) {
        echo "Arquivo não encontrado: $filename\n";
        continue;
    }
    
    $content = file_get_contents($filepath);
    $modified = false;
    
    // Atualizar navigationGroup
    $pattern = '/protected static string\|\\\\UnitEnum\|null \$navigationGroup = \'[^\']+\';/';
    $replacement = "protected static string|\\UnitEnum|null \$navigationGroup = '{$config['group']}';";
    if (preg_match($pattern, $content)) {
        $content = preg_replace($pattern, $replacement, $content);
        $modified = true;
    }
    
    // Adicionar ou atualizar navigationSort
    if (strpos($content, '$navigationSort') !== false) {
        $pattern = '/protected static \?int \$navigationSort = \d+;/';
        $replacement = "protected static ?int \$navigationSort = {$config['sort']};";
        $content = preg_replace($pattern, $replacement, $content);
    } else {
        // Adicionar após navigationGroup
        $pattern = '/(protected static string\|\\\\UnitEnum\|null \$navigationGroup = \'[^\']+\';)/';
        $replacement = "$1\n    protected static ?int \$navigationSort = {$config['sort']};";
        $content = preg_replace($pattern, $replacement, $content);
    }
    $modified = true;
    
    // Se for o CandidateResource, atualizar labels
    if ($filename === 'CandidateResource.php' && isset($config['label'])) {
        // Atualizar navigationLabel
        $content = preg_replace(
            "/protected static \?string \\\$navigationLabel = '[^']+';/",
            "protected static ?string \$navigationLabel = '{$config['label']}';",
            $content
        );
        // Atualizar modelLabel
        $content = preg_replace(
            "/protected static \?string \\\$modelLabel = '[^']+';/",
            "protected static ?string \$modelLabel = '{$config['modelLabel']}';",
            $content
        );
        // Atualizar pluralModelLabel
        $content = preg_replace(
            "/protected static \?string \\\$pluralModelLabel = '[^']+';/",
            "protected static ?string \$pluralModelLabel = '{$config['pluralLabel']}';",
            $content
        );
    }
    
    if ($modified) {
        file_put_contents($filepath, $content);
        echo "Atualizado: $filename -> {$config['group']} (sort: {$config['sort']})\n";
        $updated++;
    }
}

echo "\n=== Total de arquivos atualizados: $updated ===\n";
