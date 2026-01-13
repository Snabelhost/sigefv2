<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trainer extends Model
{
    use HasFactory;

    protected $fillable = [
        'institution_id',
        'full_name',
        'nip',
        'gender',
        'rank_id',
        'organ',
        'education_level',
        'phone',
        'trainer_type',
        'photo',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function rank()
    {
        return $this->belongsTo(Rank::class);
    }

    public function subjectAuthorizations()
    {
        return $this->hasMany(TrainerSubjectAuthorization::class);
    }
}
