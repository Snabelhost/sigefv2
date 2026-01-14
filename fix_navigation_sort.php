<?php

/**
 * Script para ajustar navigationSort dos Resources de Gestão Escolar
 */

$resources = [
    // Formadores = 1 (já está)
    // Agentes = 2 (já alterado)
    // Alistados = 3 (já alterado)
    // Instruendos = 4 (já alterado)
    'EquipmentAssignmentResource.php' => 5,
    'SubjectResource.php' => 6,
    'StudentClassResource.php' => 7,
    'StudentLeaveResource.php' => 8,
    'EvaluationResource.php' => 9,
    'SelectionTestResource.php' => 10,
];

$resourcesPath = __DIR__ . '/app/Filament/Resources/';

foreach ($resources as $filename => $sort) {
    $filepath = $resourcesPath . $filename;
    
    if (!file_exists($filepath)) {
        echo "Arquivo não encontrado: $filename\n";
        continue;
    }
    
    $content = file_get_contents($filepath);
    
    // Atualizar navigationSort
    $content = preg_replace(
        '/protected static \?int \$navigationSort = \d+;/',
        "protected static ?int \$navigationSort = $sort;",
        $content
    );
    
    file_put_contents($filepath, $content);
    echo "Atualizado: $filename -> sort: $sort\n";
}

echo "\n=== Concluído ===\n";
