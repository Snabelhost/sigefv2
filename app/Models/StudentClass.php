<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentClass extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'institution_id',
        'course_map_id',
        'name',
        'academic_year_id',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function courseMap()
    {
        return $this->belongsTo(CourseMap::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'class_students', 'class_id', 'student_id')
                    ->withPivot('enrolled_at')
                    ->withTimestamps();
    }
}
