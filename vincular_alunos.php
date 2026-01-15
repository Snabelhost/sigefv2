<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Student;
use App\Models\StudentClass;
use Illuminate\Support\Facades\DB;

$students = Student::all();
$count = 0;

foreach ($students as $student) {
    $class = StudentClass::where('institution_id', $student->institution_id)->first();
    
    if ($class) {
        $exists = DB::table('class_students')
            ->where('student_id', $student->id)
            ->where('class_id', $class->id)
            ->exists();
        
        if (!$exists) {
            DB::table('class_students')->insert([
                'student_id' => $student->id,
                'class_id' => $class->id,
                'enrolled_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $count++;
        }
    }
}

echo "Alunos vinculados às turmas: $count\n";
