<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'workload_hours', 'course_phase_id'];

    public function phase()
    {
        return $this->belongsTo(CoursePhase::class, 'course_phase_id');
    }
}
