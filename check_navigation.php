<?php

$files = [
    'Cluster Formandos' => 'app/Filament/Clusters/Formandos.php',
    'AgentResource' => 'app/Filament/Resources/AgentResource.php',
    'CandidateResource' => 'app/Filament/Resources/CandidateResource.php',
    'StudentResource' => 'app/Filament/Resources/StudentResource.php',
    'TrainerResource' => 'app/Filament/Resources/TrainerResource.php',
    'EquipmentAssignmentResource' => 'app/Filament/Resources/EquipmentAssignmentResource.php',
    'SubjectResource' => 'app/Filament/Resources/SubjectResource.php',
];

foreach ($files as $name => $path) {
    echo "=== $name ===\n";
    $fullPath = __DIR__ . '/' . $path;
    
    if (file_exists($fullPath)) {
        $content = file_get_contents($fullPath);
        
        // Procurar cluster
        if (preg_match('/\$cluster\s*=\s*([^;]+);/', $content, $m)) {
            echo "cluster: " . trim($m[1]) . "\n";
        } else {
            echo "cluster: NAO DEFINIDO\n";
        }
        
        // Procurar navigationGroup
        if (preg_match('/\$navigationGroup\s*=\s*\'([^\']+)\'/', $content, $m)) {
            echo "navigationGroup: " . $m[1] . "\n";
        } else {
            echo "navigationGroup: NAO DEFINIDO\n";
        }
        
        // Procurar navigationSort
        if (preg_match('/\$navigationSort\s*=\s*(\d+)/', $content, $m)) {
            echo "navigationSort: " . $m[1] . "\n";
        } else {
            echo "navigationSort: NAO DEFINIDO\n";
        }
        
        // Procurar navigationLabel
        if (preg_match('/\$navigationLabel\s*=\s*\'([^\']+)\'/', $content, $m)) {
            echo "navigationLabel: " . $m[1] . "\n";
        }
    } else {
        echo "ARQUIVO NAO ENCONTRADO: $fullPath\n";
    }
    echo "\n";
}
