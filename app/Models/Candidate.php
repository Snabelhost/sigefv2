<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'recruitment_type_id',
        'full_name',
        'id_number',
        'gender',
        'birth_date',
        'marital_status',
        'education_level',
        'education_area',
        'phone',
        'email',
        'father_name',
        'mother_name',
        'provenance_id',
        'current_rank_id',
        'pna_entry_date',
        'photo',
        'status',
        'academic_year_id',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'pna_entry_date' => 'date',
    ];

    public function recruitmentType()
    {
        return $this->belongsTo(RecruitmentType::class);
    }

    public function provenance()
    {
        return $this->belongsTo(Provenance::class);
    }

    public function rank()
    {
        return $this->belongsTo(Rank::class, 'current_rank_id');
    }

    public function currentRank()
    {
        return $this->belongsTo(Rank::class, 'current_rank_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function testResults()
    {
        return $this->hasMany(CandidateTestResult::class);
    }

    public function documents()
    {
        return $this->hasMany(CandidateDocument::class);
    }
}
