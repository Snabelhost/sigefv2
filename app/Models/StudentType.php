<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'color',
        'order',
        'has_phase',
        'phase_name',
        'is_active',
    ];

    protected $casts = [
        'has_phase' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function students()
    {
        return $this->hasMany(Student::class, 'student_type_id');
    }

    public static function getColorOptions(): array
    {
        return [
            'gray' => 'Cinza (Neutro)',
            'primary' => 'Azul (Primário)',
            'secondary' => 'Roxo (Secundário)',
            'success' => 'Verde (Sucesso)',
            'warning' => 'Amarelo (Aviso)',
            'danger' => 'Vermelho (Perigo)',
            'info' => 'Ciano (Informação)',
        ];
    }
}
