<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentClass;
use App\Models\StudentLeave;
use App\Models\Institution;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Gera a pauta de notas de uma turma
     */
    public function studentGrades(StudentClass $class)
    {
        $students = Student::with(['candidate', 'evaluations.subject'])
            ->where('id', '!=', null) // Placeholder - should filter by class
            ->get();

        $pdf = Pdf::loadView('reports.student-grades', [
            'class' => $class,
            'students' => $students,
            'generatedAt' => now(),
        ]);

        return $pdf->download("pauta-notas-{$class->name}.pdf");
    }

    /**
     * Lista geral de formandos por escola
     */
    public function studentList(Institution $institution)
    {
        $students = Student::with(['candidate', 'courseMap'])
            ->where('institution_id', $institution->id)
            ->where('status', 'frequenta')
            ->orderBy('student_number')
            ->get();

        $pdf = Pdf::loadView('reports.student-list', [
            'institution' => $institution,
            'students' => $students,
            'generatedAt' => now(),
        ]);

        return $pdf->download("lista-formandos-{$institution->acronym}.pdf");
    }

    /**
     * Guia de marcha individual
     */
    public function marchGuide(Student $student)
    {
        $student->load(['candidate', 'institution', 'courseMap']);

        $pdf = Pdf::loadView('reports.march-guide', [
            'student' => $student,
            'generatedAt' => now(),
        ]);

        return $pdf->download("guia-marcha-{$student->student_number}.pdf");
    }

    /**
     * Lista de candidatos aprovados para admissão
     */
    public function approvedCandidates(Request $request)
    {
        $candidates = \App\Models\Candidate::with(['provenance', 'recruitmentType'])
            ->where('status', 'approved')
            ->when($request->academic_year_id, fn($q, $year) => $q->where('academic_year_id', $year))
            ->orderBy('full_name')
            ->get();

        $pdf = Pdf::loadView('reports.approved-candidates', [
            'candidates' => $candidates,
            'generatedAt' => now(),
        ]);

        return $pdf->download('candidatos-aprovados.pdf');
    }

    /**
     * Mapa de faltas/dispensas por escola
     */
    public function absenceReport(Institution $institution)
    {
        $leaves = StudentLeave::with(['student.candidate'])
            ->where('institution_id', $institution->id)
            ->orderBy('start_date', 'desc')
            ->get();

        $pdf = Pdf::loadView('reports.absence-report', [
            'institution' => $institution,
            'leaves' => $leaves,
            'generatedAt' => now(),
        ]);

        return $pdf->download("mapa-faltas-{$institution->acronym}.pdf");
    }

    /**
     * Histórico académico individual do formando
     */
    public function studentHistory(Student $student)
    {
        $student->load([
            'candidate.provenance',
            'institution',
            'evaluations.subject',
            'leaves',
        ]);

        $pdf = Pdf::loadView('reports.student-history', [
            'student' => $student,
            'generatedAt' => now(),
        ]);

        return $pdf->download("historico-{$student->student_number}.pdf");
    }
}
