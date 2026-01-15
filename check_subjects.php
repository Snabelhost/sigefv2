<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Subject;
use App\Models\Institution;

$institutions = Institution::all();

foreach ($institutions as $inst) {
    $count = Subject::where('institution_id', $inst->id)->count();
    echo $inst->name . ': ' . $count . ' disciplinas' . PHP_EOL;
}

echo "\nTotal de disciplinas: " . Subject::count() . PHP_EOL;
