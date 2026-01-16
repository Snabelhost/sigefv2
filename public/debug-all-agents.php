<?php

/**
 * Debug - Ver todos os agentes e seus candidate_id
 */

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

use App\Models\Student;

echo "<h1>Debug - Todos os Agentes (Formandos Superiores)</h1>";

$agents = Student::where('student_type', 'Formando Superior')
    ->whereIn('status', ['em_formacao', 'concluiu'])
    ->with('candidate')
    ->orderBy('id', 'desc')
    ->get();

echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
echo "<tr style='background-color: #333; color: white;'>";
echo "<th>ID</th><th>NIP</th><th>Institution ID</th><th>candidate_id</th><th>Candidato Nome</th><th>Status</th>";
echo "</tr>";

foreach ($agents as $agent) {
    $hasProblem = empty($agent->candidate_id) || !$agent->candidate;
    $bgColor = $hasProblem ? '#ffcccc' : '#ccffcc';
    
    echo "<tr style='background-color: {$bgColor}'>";
    echo "<td>{$agent->id}</td>";
    echo "<td>{$agent->nuri}</td>";
    echo "<td>{$agent->institution_id}</td>";
    echo "<td>" . ($agent->candidate_id ?? '<span style="color:red">NULL</span>') . "</td>";
    echo "<td>" . ($agent->candidate?->full_name ?? '<span style="color:red">SEM NOME</span>') . "</td>";
    echo "<td>" . ($hasProblem ? '❌' : '✅') . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>";
echo "<p><a href='/admin/agents'>Admin Agents</a> | <a href='/escola/1/agents'>Escola 1 Agents</a></p>";
