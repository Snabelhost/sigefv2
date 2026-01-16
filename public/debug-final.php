<?php

/**
 * Debug FINAL - Comparar registros que funcionam vs que não funcionam
 */

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

use App\Models\Student;
use App\Models\Candidate;
use Illuminate\Support\Facades\DB;

echo "<h1>Debug FINAL - Comparação de Registros</h1>";

// Todos os agentes da Escola 1
$agents = Student::where('student_type', 'Formando Superior')
    ->whereIn('status', ['em_formacao', 'concluiu'])
    ->where('institution_id', 1)
    ->with('candidate')
    ->get();

echo "<h2>Comparativo detalhado:</h2>";
echo "<table border='1' cellpadding='10' style='border-collapse:collapse; width:100%;'>";
echo "<tr style='background:#333; color:white;'>";
echo "<th>ID</th><th>NIP</th><th>candidate_id</th><th>ACCESSOR full_name</th><th>candidate->full_name</th><th>created_at</th><th>Funciona UI?</th>";
echo "</tr>";

$workingNips = ['3214124124', '113131313', '34124124'];
$brokenNips = ['423444234', '3434244', '234234234'];

foreach ($agents as $agent) {
    $accessorValue = $agent->full_name;
    $relationValue = $agent->candidate?->full_name ?? 'NULL';
    $works = in_array($agent->nuri, $workingNips);
    
    $bgColor = $works ? '#e0ffe0' : '#ffe0e0';
    
    echo "<tr style='background: {$bgColor};'>";
    echo "<td>{$agent->id}</td>";
    echo "<td><strong>{$agent->nuri}</strong></td>";
    echo "<td>{$agent->candidate_id}</td>";
    echo "<td><strong style='color:blue;'>{$accessorValue}</strong></td>";
    echo "<td>{$relationValue}</td>";
    echo "<td>{$agent->created_at}</td>";
    echo "<td>" . ($works ? "✅ SIM" : "❌ NÃO") . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>Verificar IDs de candidates diretamente:</h2>";
$candidateIds = $agents->pluck('candidate_id')->filter()->unique();
echo "<p>IDs consultados: " . $candidateIds->implode(', ') . "</p>";

$candidates = Candidate::whereIn('id', $candidateIds)->get();
echo "<table border='1' cellpadding='10' style='border-collapse:collapse;'>";
echo "<tr style='background:#333; color:white;'><th>ID</th><th>full_name</th><th>id_number</th></tr>";
foreach ($candidates as $c) {
    echo "<tr><td>{$c->id}</td><td><strong>{$c->full_name}</strong></td><td>{$c->id_number}</td></tr>";
}
echo "</table>";
