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
        'provenance_id',
        'rank_id',
        'course_map_id',
        'student_number',
        'student_type',
        'student_type_id',
        'status',
        'nuri',
        'phone',
        'cia',
        'platoon',
        'section',
        'current_phase_id',
        'enrollment_date',
        'conclusion_date',
        'photo',
        'bilhete_identidade',
        'certificado_doc',
        'carta_conducao',
        'passaporte',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'conclusion_date' => 'date',
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function provenance()
    {
        return $this->belongsTo(Provenance::class);
    }

    public function rank()
    {
        return $this->belongsTo(Rank::class);
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

    public function classes()
    {
        return $this->belongsToMany(StudentClass::class, 'class_students', 'student_id', 'class_id')
                    ->withPivot('enrolled_at')
                    ->withTimestamps();
    }

    public function classEnrollments()
    {
        return $this->hasMany(StudentClassEnrollment::class);
    }

    public function subjectEnrollments()
    {
        return $this->hasMany(StudentSubjectEnrollment::class);
    }

    public function getFullNameAttribute()
    {
        return $this->candidate ? $this->candidate->full_name : 'N/A';
    }

    public function studentTypeRelation()
    {
        return $this->belongsTo(StudentType::class, 'student_type_id');
    }

    public function getStudentTypeLabel()
    {
        // Se tiver relação com StudentType, usa o nome da tabela
        if ($this->studentTypeRelation) {
            return $this->studentTypeRelation->name;
        }

        // Fallback para compatibilidade
        return match($this->student_type) {
            'Alistado' => 'Alistado',
            'Recruta' => '1ª Fase - Recruta',
            'Instruendo' => '2ª Fase - Instruendo',
            'Agente' => 'Formado - Agente',
            default => $this->student_type,
        };
    }

    public function getStudentTypeColor()
    {
        if ($this->studentTypeRelation) {
            return $this->studentTypeRelation->color;
        }

        return match($this->student_type) {
            'Alistado' => 'gray',
            'Recruta' => 'warning',
            'Instruendo' => 'info',
            'Agente' => 'success',
            default => 'gray',
        };
    }
}
