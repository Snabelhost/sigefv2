<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoursePlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'academic_year_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'course_plan_subjects')
                    ->withPivot('order')
                    ->withTimestamps();
    }
}
