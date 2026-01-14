<?php

/**
 * Script para reorganizar os grupos de navegação - Nova Estrutura
 */

$resourceGroups = [
    // CURRÍCULO
    'CourseMapResource.php' => ['group' => 'Currículo', 'sort' => 1, 'label' => 'Mapas de Curso'],
    'CoursePlanResource.php' => ['group' => 'Currículo', 'sort' => 2, 'label' => 'Planos de Curso'],
    'CoursePhaseResource.php' => ['group' => 'Currículo', 'sort' => 3, 'label' => 'Fases de Curso'],
    'CourseResource.php' => ['group' => 'Currículo', 'sort' => 4, 'label' => 'Cursos'],
    
    // GESTÃO ESCOLAR (exceto os que estão no Cluster Formandos)
    'TrainerResource.php' => ['group' => 'Gestão Escolar', 'sort' => 1],
    'EquipmentAssignmentResource.php' => ['group' => 'Gestão Escolar', 'sort' => 3],
    'SubjectResource.php' => ['group' => 'Gestão Escolar', 'sort' => 4],
    'StudentClassResource.php' => ['group' => 'Gestão Escolar', 'sort' => 5],
    'StudentLeaveResource.php' => ['group' => 'Gestão Escolar', 'sort' => 6],
    'EvaluationResource.php' => ['group' => 'Gestão Escolar', 'sort' => 7],
    'SelectionTestResource.php' => ['group' => 'Gestão Escolar', 'sort' => 8],
    
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
    
    // Atualizar navigationSort
    $pattern = '/protected static \?int \$navigationSort = \d+;/';
    $replacement = "protected static ?int \$navigationSort = {$config['sort']};";
    if (preg_match($pattern, $content)) {
        $content = preg_replace($pattern, $replacement, $content);
    } else {
        // Adicionar após navigationGroup
        $pattern = '/(protected static string\|\\\\UnitEnum\|null \$navigationGroup = \'[^\']+\';)/';
        $replacement = "$1\n    protected static ?int \$navigationSort = {$config['sort']};";
        $content = preg_replace($pattern, $replacement, $content);
    }
    $modified = true;
    
    if ($modified) {
        file_put_contents($filepath, $content);
        echo "Atualizado: $filename -> {$config['group']} (sort: {$config['sort']})\n";
        $updated++;
    }
}

echo "\n=== Total de arquivos atualizados: $updated ===\n";
