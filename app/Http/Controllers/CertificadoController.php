<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentClass;
use App\Models\Institution;
use App\Models\Evaluation;
use Illuminate\Http\Request;

class CertificadoController extends Controller
{
    public function gerar(Request $request)
    {
        $filters = $request->only(['cia', 'pelotao', 'seccao', 'turma', 'institution_id']);
        
        $query = Student::with(['candidate', 'classes.institution', 'classes.courseMap.course', 'evaluations'])
            ->whereHas('evaluations');
        
        if (!empty($filters['cia'])) {
            $query->where('cia', $filters['cia']);
        }
        if (!empty($filters['pelotao'])) {
            $query->where('pelotao', $filters['pelotao']);
        }
        if (!empty($filters['seccao'])) {
            $query->where('seccao', $filters['seccao']);
        }
        if (!empty($filters['turma'])) {
            $query->whereHas('classes', fn ($q) => $q->where('classes.id', $filters['turma']));
        }
        if (!empty($filters['institution_id'])) {
            $query->whereHas('classes', fn ($q) => $q->where('institution_id', $filters['institution_id']));
        }
        
        $students = $query->get()->filter(fn ($s) => $this->calculateAverage($s) >= 10);
        
        return view('exports.certificados', [
            'alunos' => $students->map(fn ($s) => [
                'numero' => $s->student_number,
                'nome' => $s->candidate?->full_name,
                'bi' => $s->candidate?->id_number,
                'cia' => $s->cia,
                'pelotao' => $s->platoon,
                'seccao' => $s->section,
                'turma' => $s->classes->first()?->name,
                'curso' => $s->classes->first()?->courseMap?->course?->name,
                'instituicao' => $s->classes->first()?->institution?->name,
                'media' => number_format($this->calculateAverage($s), 1),
            ]),
            'titulo' => 'Certificados de Aprovação',
            'filtros' => $filters,
        ]);
    }

    public function individual(Student $student)
    {
        $student->load(['candidate', 'classes.institution', 'classes.courseMap.course', 'evaluations.subject']);
        
        $avg = $this->calculateAverage($student);
        
        // Agrupar notas por disciplina
        $disciplinas = $student->evaluations
            ->groupBy('subject_id')
            ->map(function ($evals) {
                $subject = $evals->first()->subject;
                return [
                    'nome' => $subject?->name ?? 'Disciplina',
                    'nota' => $evals->avg('score'),
                ];
            })
            ->values()
            ->toArray();
        
        return view('exports.certificado-individual', [
            'aluno' => [
                'numero' => $student->student_number,
                'numero_registo' => $student->student_number,
                'nome' => $student->candidate?->full_name,
                'bi' => $student->candidate?->id_number,
                'nascimento' => $student->candidate?->birth_date?->format('d/m/Y'),
                'cia' => $student->cia,
                'pelotao' => $student->platoon,
                'seccao' => $student->section,
                'turma' => $student->classes->first()?->name,
                'curso' => $student->classes->first()?->courseMap?->course?->name,
                'instituicao' => $student->classes->first()?->institution?->name,
                'cidade' => $student->classes->first()?->institution?->city ?? 'Luanda',
                'ano_instrucao' => $student->classes->first()?->academicYear?->year ?? date('Y'),
                'media' => number_format($avg, 1),
                'resultado' => $avg >= 10 ? 'APROVADO' : 'REPROVADO',
                'disciplinas' => $disciplinas,
            ],
        ]);
    }

    public function bulk(Request $request)
    {
        $ids = explode(',', $request->ids);
        
        $students = Student::with(['candidate', 'classes.institution', 'classes.courseMap.course', 'evaluations'])
            ->whereIn('id', $ids)
            ->get()
            ->filter(fn ($s) => $this->calculateAverage($s) >= 10);
        
        return view('exports.certificados', [
            'alunos' => $students->map(fn ($s) => [
                'numero' => $s->student_number,
                'nome' => $s->candidate?->full_name,
                'bi' => $s->candidate?->id_number,
                'cia' => $s->cia,
                'pelotao' => $s->platoon,
                'seccao' => $s->section,
                'turma' => $s->classes->first()?->name,
                'curso' => $s->classes->first()?->courseMap?->course?->name,
                'instituicao' => $s->classes->first()?->institution?->name,
                'media' => number_format($this->calculateAverage($s), 1),
            ]),
            'titulo' => 'Certificados de Aprovação',
        ]);
    }

    protected function calculateAverage(Student $student): float
    {
        $evaluations = $student->evaluations;
        
        if ($evaluations->isEmpty()) {
            return 0;
        }
        
        return $evaluations->avg('score');
    }
}
