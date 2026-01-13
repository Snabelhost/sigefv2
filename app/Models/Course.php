<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'duration_months', 'has_phases'];

    protected $casts = [
        'has_phases' => 'boolean',
    ];

    public function phases()
    {
        return $this->hasMany(CoursePhase::class);
    }
}
