<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_id',
        'institution_id',
        'course_map_id',
        'student_number',
        'student_type',
        'status',
        'nuri',
        'cia',
        'platoon',
        'section',
        'current_phase_id',
        'enrollment_date',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function courseMap()
    {
        return $this->belongsTo(CourseMap::class);
    }

    public function currentPhase()
    {
        return $this->belongsTo(CoursePhase::class, 'current_phase_id');
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }

    public function leaves()
    {
        return $this->hasMany(StudentLeave::class);
    }
}
