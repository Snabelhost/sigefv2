<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\StudentClass;

$classes = StudentClass::with('students')->get();

foreach ($classes as $class) {
    echo $class->name . ': ' . $class->students->count() . ' alunos' . PHP_EOL;
}
