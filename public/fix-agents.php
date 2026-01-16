<?php

/**
 * Script para verificar e corrigir vínculos Agentes-Candidatos
 * Aceder em: http://sigefv2.test/fix-agents.php
 */

// Bootstrap Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

use App\Models\Student;
use App\Models\Candidate;

echo "<h1>Verificar e Corrigir Agentes</h1>";

// Buscar todos os Students (Agentes) do tipo Formando Superior
$agents = Student::where('student_type', 'Formando Superior')
    ->whereIn('status', ['em_formacao', 'concluiu'])
    ->with('candidate')
    ->get();

echo "<p>Encontrados <strong>" . $agents->count() . "</strong> agentes (Formando Superior)</p>";

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>NIP</th><th>candidate_id</th><th>Nome do Candidato</th><th>Status</th></tr>";

$problemCount = 0;

foreach ($agents as $agent) {
    $candidateName = $agent->candidate?->full_name ?? 'SEM CANDIDATO';
    $hasProblems = empty($agent->candidate) || empty($agent->candidate->full_name) || strpos($agent->candidate->full_name, 'Agente NIP:') === 0;
    
    $bgColor = $hasProblems ? '#ffcccc' : '#ccffcc';
    
    if ($hasProblems) {
        $problemCount++;
    }
    
    echo "<tr style='background-color: {$bgColor}'>";
    echo "<td>{$agent->id}</td>";
    echo "<td>{$agent->nuri}</td>";
    echo "<td>{$agent->candidate_id}</td>";
    echo "<td>{$candidateName}</td>";
    echo "<td>" . ($hasProblems ? '⚠️ Problema' : '✅ OK') . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>Resumo</h2>";
echo "<p><strong>Total de agentes:</strong> " . $agents->count() . "</p>";
echo "<p><strong>Com problemas:</strong> {$problemCount}</p>";
echo "<p><strong>OK:</strong> " . ($agents->count() - $problemCount) . "</p>";

// Verificar se há candidatos correspondentes pelo NIP
echo "<h2>Candidatos disponíveis por NIP</h2>";

foreach ($agents as $agent) {
    if (!empty($agent->nuri)) {
        $matchingCandidate = Candidate::where('id_number', $agent->nuri)->first();
        if ($matchingCandidate) {
            echo "<p>NIP {$agent->nuri}: Candidato ID {$matchingCandidate->id} - <strong>{$matchingCandidate->full_name}</strong></p>";
        } else {
            echo "<p style='color:red'>NIP {$agent->nuri}: ❌ Nenhum candidato encontrado na tabela candidates</p>";
        }
    }
}

echo "<hr>";
echo "<p><a href='/admin/agents'>Admin Agents</a> | <a href='/escola/1/agents'>Escola Agents</a></p>";
