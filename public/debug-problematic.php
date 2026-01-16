<?php

/**
 * Debug - Ver detalhes específicos dos agentes problemáticos
 */

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

use App\Models\Student;
use App\Models\Candidate;

echo "<h1>Debug - Agentes Problemáticos (NIPs sem nome visível)</h1>";

// NIPs que estão sem nome no painel
$problematicNips = ['423444234', '3434244', '234234234'];

echo "<h2>Agentes problemáticos:</h2>";
foreach ($problematicNips as $nip) {
    $agent = Student::where('nuri', $nip)->first();
    
    if (!$agent) {
        echo "<p style='color:red'>NIP {$nip}: Agente não encontrado</p>";
        continue;
    }
    
    echo "<div style='border:2px solid red; padding:15px; margin:10px; background:#fff0f0;'>";
    echo "<h3>Agente NIP: {$nip}</h3>";
    echo "<ul>";
    echo "<li><strong>Student ID:</strong> {$agent->id}</li>";
    echo "<li><strong>institution_id:</strong> {$agent->institution_id}</li>";
    echo "<li><strong>candidate_id:</strong> " . ($agent->candidate_id ?? 'NULL') . "</li>";
    
    // Verificar relação
    echo "<li><strong>Relação candidate carregada:</strong> " . ($agent->relationLoaded('candidate') ? 'SIM' : 'NÃO') . "</li>";
    
    // Forçar load
    $agent->load('candidate');
    echo "<li><strong>Após load('candidate'):</strong> " . ($agent->relationLoaded('candidate') ? 'SIM' : 'NÃO') . "</li>";
    
    // Verificar candidato
    if ($agent->candidate) {
        echo "<li style='color:green;'><strong>Candidato:</strong> {$agent->candidate->full_name} (ID: {$agent->candidate->id})</li>";
    } else {
        echo "<li style='color:red;'><strong>Candidato:</strong> NULL (relação não retornou nada)</li>";
        
        // Verificar diretamente na BD
        if ($agent->candidate_id) {
            $directCandidate = Candidate::find($agent->candidate_id);
            if ($directCandidate) {
                echo "<li style='color:orange;'><strong>Candidate::find({$agent->candidate_id}):</strong> {$directCandidate->full_name}</li>";
            } else {
                echo "<li style='color:red;'><strong>Candidate::find({$agent->candidate_id}):</strong> NÃO EXISTE na BD</li>";
            }
        }
    }
    echo "</ul>";
    echo "</div>";
}

echo "<hr>";

echo "<h2>Agentes que funcionam (para comparação):</h2>";
$workingNips = ['3214124124', '113131313', '34124124'];
foreach ($workingNips as $nip) {
    $agent = Student::where('nuri', $nip)->first();
    if ($agent) {
        $agent->load('candidate');
        echo "<div style='border:2px solid green; padding:15px; margin:10px; background:#f0fff0;'>";
        echo "<p><strong>NIP {$nip}:</strong> candidate_id={$agent->candidate_id}, Nome: {$agent->candidate?->full_name}</p>";
        echo "</div>";
    }
}
