<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseMap extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'institution_id',
        'academic_year_id',
        'organ',
        'max_students',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function getFullTitleAttribute(): string
    {
        return "{$this->course?->name} ({$this->institution?->acronym}) - {$this->academicYear?->year}";
    }
}
