<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainerSubjectAuthorization extends Model
{
    use HasFactory;

    protected $fillable = [
        'trainer_id',
        'subject_id',
        'course_id',
        'authorized_at',
        'authorized_by',
    ];

    protected $casts = [
        'authorized_at' => 'datetime',
    ];

    public function trainer()
    {
        return $this->belongsTo(Trainer::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function authorizer()
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }
}
