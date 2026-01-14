<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'institution_id',
        'name',
        'description',
        'duration_months',
        'has_phases',
    ];

    protected $casts = [
        'has_phases' => 'boolean',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function phases()
    {
        return $this->hasMany(CoursePhase::class);
    }
}
