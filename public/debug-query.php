<?php

/**
 * Debug - Simular EXATAMENTE a query do Filament Escola AgentResource
 */

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

use App\Models\Student;
use Illuminate\Support\Facades\DB;

echo "<h1>Debug Query Filament Escola AgentResource</h1>";

// Ativar query log
DB::enableQueryLog();

// Simular EXATAMENTE a query do getEloquentQuery do AgentResource Escola
$query = Student::query()
    ->whereIn('status', ['em_formacao', 'concluiu'])
    ->where('student_type', 'Formando Superior')
    ->with(['candidate', 'institution', 'provenance', 'rank']);

// Simular o filtro do tenant (institution_id = 1)
$query->where('institution_id', 1);

$agents = $query->get();

echo "<h2>Query Log:</h2>";
echo "<pre>";
print_r(DB::getQueryLog());
echo "</pre>";

echo "<h2>Resultados: " . $agents->count() . " agentes</h2>";

foreach ($agents as $agent) {
    echo "<div style='border:1px solid #ccc; padding:10px; margin:10px; background:#f9f9f9;'>";
    echo "<h3>Agente ID: {$agent->id} | NIP: {$agent->nuri}</h3>";
    echo "<p><strong>candidate_id (atributo):</strong> " . var_export($agent->candidate_id, true) . "</p>";
    echo "<p><strong>getOriginal('candidate_id'):</strong> " . var_export($agent->getOriginal('candidate_id'), true) . "</p>";
    echo "<p><strong>Relação candidate carregada:</strong> " . ($agent->relationLoaded('candidate') ? 'SIM' : 'NAO') . "</p>";
    
    if ($agent->candidate) {
        echo "<p style='color:green;'><strong>Candidato:</strong> {$agent->candidate->full_name} (ID: {$agent->candidate->id})</p>";
    } else {
        echo "<p style='color:red;'><strong>Candidato:</strong> NULL</p>";
        
        // Verificar diretamente
        $directCheck = \App\Models\Candidate::find($agent->candidate_id);
        if ($directCheck) {
            echo "<p style='color:orange;'>Mas Candidate::find({$agent->candidate_id}) retorna: {$directCheck->full_name}</p>";
        }
    }
    
    echo "</div>";
}

echo "<h2>Verificar candidatos IDs 314 e 317:</h2>";
$candidates = \App\Models\Candidate::whereIn('id', [314, 317])->get();
foreach ($candidates as $c) {
    echo "<p>Candidato ID {$c->id}: <strong>{$c->full_name}</strong></p>";
}
