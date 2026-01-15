<?php

namespace App\Http\Controllers;

use App\Models\StudentClass;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Evaluation;
use App\Models\Institution;
use Illuminate\Http\Request;

class PautaController extends Controller
{
    public function miniPautaPrint(Request $request)
    {
        $turma = StudentClass::with(['academicYear', 'courseMap.course'])->findOrFail($request->turma);
        $disciplina = Subject::find($request->disciplina);
        $instituicao = Institution::find($turma->institution_id);
        
        $students = Student::whereHas('classes', fn ($q) => $q->where('classes.id', $turma->id))
            ->with(['candidate'])
            ->get();

        $alunos = $students->map(function ($s) use ($request) {
            return [
                'numero' => $s->student_number,
                'nome' => $s->candidate?->full_name,
                'freq1' => $this->getEvaluationScore($s->id, $request->disciplina, 'frequencia', 1),
                'freq2' => $this->getEvaluationScore($s->id, $request->disciplina, 'frequencia', 2),
                'media_freq' => $this->calculateAverage($s->id, $request->disciplina, 'frequencia'),
                'exame' => $this->getEvaluationScore($s->id, $request->disciplina, 'exame', 1),
                'media_final' => $this->calculateFinalAverage($s->id, $request->disciplina),
                'resultado' => $this->calculateFinalAverage($s->id, $request->disciplina) >= 10 ? 'Aprovado' : 'Reprovado',
            ];
        });

        return view('exports.mini-pauta', [
            'turma' => $turma,
            'disciplina' => $disciplina,
            'alunos' => $alunos,
            'instituicao' => $instituicao,
        ]);
    }

    public function pautaGeralPrint(Request $request)
    {
        $turma = StudentClass::with(['academicYear', 'courseMap.course'])->findOrFail($request->turma);
        $instituicao = Institution::find($turma->institution_id);
        $disciplinas = Subject::where('institution_id', $turma->institution_id)->get();
        
        $students = Student::whereHas('classes', fn ($q) => $q->where('classes.id', $turma->id))
            ->with(['candidate', 'evaluations'])
            ->get();

        $alunos = $students->map(function ($s) use ($disciplinas) {
            $medias = [];
            foreach ($disciplinas as $sub) {
                $medias[$sub->id] = $this->getSubjectFinalAverage($s->id, $sub->id);
            }
            return [
                'numero' => $s->student_number,
                'nome' => $s->candidate?->full_name,
                'medias' => $medias,
                'media_geral' => $this->calculateGeneralAverage($s->id, $disciplinas),
                'resultado' => $this->getResult($s->id, $disciplinas),
            ];
        });

        return view('exports.pauta-geral', [
            'turma' => $turma,
            'disciplinas' => $disciplinas,
            'alunos' => $alunos,
            'instituicao' => $instituicao,
        ]);
    }

    protected function getEvaluationScore($studentId, $subjectId, $type, $order)
    {
        $evaluation = Evaluation::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->where('evaluation_type', $type)
            ->where('observations', 'order_' . $order)
            ->first();
        
        return $evaluation?->score;
    }

    protected function calculateAverage($studentId, $subjectId, $type)
    {
        $evaluations = Evaluation::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->where('evaluation_type', $type)
            ->pluck('score')
            ->filter();
        
        if ($evaluations->isEmpty()) {
            return '-';
        }
        
        return number_format($evaluations->avg(), 1);
    }

    protected function calculateFinalAverage($studentId, $subjectId)
    {
        $mediaFreq = $this->calculateAverage($studentId, $subjectId, 'frequencia');
        $exame = $this->getEvaluationScore($studentId, $subjectId, 'exame', 1);
        
        if ($mediaFreq === '-' && !$exame) {
            return '-';
        }
        
        $mediaFreqValue = $mediaFreq !== '-' ? floatval($mediaFreq) : 0;
        $exameValue = $exame ? floatval($exame) : 0;
        
        if ($mediaFreqValue > 0 && $exameValue > 0) {
            $mediaFinal = ($mediaFreqValue * 0.4) + ($exameValue * 0.6);
            return number_format($mediaFinal, 1);
        }
        
        return '-';
    }

    protected function getSubjectFinalAverage($studentId, $subjectId)
    {
        $evaluations = Evaluation::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->pluck('score')
            ->filter();
        
        if ($evaluations->isEmpty()) {
            return '-';
        }
        
        return number_format($evaluations->avg(), 1);
    }

    protected function calculateGeneralAverage($studentId, $subjects)
    {
        $averages = [];

        foreach ($subjects as $subject) {
            $avg = $this->getSubjectFinalAverage($studentId, $subject->id);
            if ($avg !== '-') {
                $averages[] = floatval($avg);
            }
        }

        if (empty($averages)) {
            return '-';
        }

        return number_format(array_sum($averages) / count($averages), 1);
    }

    protected function getResult($studentId, $subjects)
    {
        $avg = $this->calculateGeneralAverage($studentId, $subjects);
        
        if ($avg === '-') {
            return 'Pendente';
        }
        
        return floatval($avg) >= 10 ? 'Aprovado' : 'Reprovado';
    }
}
