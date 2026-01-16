<?php

/**
 * Debug - Ver detalhes de agentes específicos
 */

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

use App\Models\Student;

echo "<h1>Debug Agentes - NIP 3434244 e 234234234</h1>";

$agents = Student::whereIn('nuri', ['3434244', '234234234'])->with('candidate')->get();

foreach ($agents as $agent) {
    echo "<h2>Agente ID: {$agent->id}</h2>";
    echo "<ul>";
    echo "<li><strong>NIP:</strong> {$agent->nuri}</li>";
    echo "<li><strong>institution_id:</strong> {$agent->institution_id}</li>";
    echo "<li><strong>student_type:</strong> {$agent->student_type}</li>";
    echo "<li><strong>status:</strong> {$agent->status}</li>";
    echo "<li><strong>candidate_id:</strong> {$agent->candidate_id}</li>";
    echo "<li><strong>Candidato:</strong> " . ($agent->candidate ? $agent->candidate->full_name . " (ID: {$agent->candidate->id})" : 'NULL') . "</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p>Na Escola com ID=1, os agentes precisam ter institution_id=1 para aparecer.</p>";
