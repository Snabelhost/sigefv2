<?php

namespace App\Filament\Resources\PautaResource\Pages;

use App\Filament\Resources\PautaResource;
use App\Models\StudentClass;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Evaluation;
use App\Models\Course;
use App\Models\Trainer;
use App\Models\TrainerSubjectAuthorization;
use Filament\Resources\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class MiniPauta extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string $resource = PautaResource::class;

    public ?int $course_id = null;
    public ?int $class_id = null;
    public ?int $subject_id = null;
    public bool $showTable = false;

    public function getView(): string
    {
        return 'filament.resources.pauta-resource.pages.mini-pauta';
    }

    public function mount(): void
    {
        $this->course_id = null;
        $this->class_id = null;
        $this->subject_id = null;
        $this->showTable = false;
    }

    public function getTitle(): string
    {
        return 'Mini Pauta do Professor';
    }

    public function getBreadcrumbs(): array
    {
        return [
            PautaResource::getUrl() => 'Pautas',
            '#' => 'Mini Pauta',
        ];
    }

    public function getClasses()
    {
        if (!$this->course_id) {
            return collect();
        }
        return StudentClass::with('institution')
            ->whereHas('courseMap', fn ($q) => $q->where('course_id', $this->course_id))
            ->get();
    }

    public function getSelectedClass(): ?StudentClass
    {
        if (!$this->class_id) {
            return null;
        }
        return StudentClass::with(['institution', 'academicYear', 'courseMap.course'])->find($this->class_id);
    }

    public function getSelectedSubject(): ?Subject
    {
        if (!$this->subject_id) {
            return null;
        }
        return Subject::find($this->subject_id);
    }

    public function getTrainerName(): string
    {
        if (!$this->subject_id) {
            return '-';
        }
        
        $authorization = TrainerSubjectAuthorization::with('trainer')
            ->where('subject_id', $this->subject_id)
            ->first();
        
        return $authorization?->trainer?->full_name ?? '-';
    }

    public function updatedCourseId(): void
    {
        $this->class_id = null;
        $this->showTable = false;
    }

    public function updatedClassId(): void
    {
        $this->showTable = false;
    }

    public function updatedSubjectId(): void
    {
        $this->showTable = false;
    }

    public function pesquisar(): void
    {
        if ($this->class_id && $this->subject_id) {
            $this->showTable = true;
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportPdf')
                ->label('Exportar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('primary')
                ->action(fn () => $this->exportPdf())
                ->disabled(fn () => !$this->showTable),
            Action::make('voltar')
                ->label('Voltar')
                ->icon('heroicon-o-arrow-left')
                ->url(PautaResource::getUrl())
                ->color('gray'),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Student::query()
                    ->when($this->class_id && $this->showTable, fn (Builder $query) => 
                        $query->whereHas('classes', fn (Builder $q) => $q->where('classes.id', $this->class_id))
                    )
                    ->when(!$this->showTable, fn (Builder $query) => $query->whereRaw('1 = 0'))
                    ->with(['candidate', 'evaluations' => fn ($q) => $q->where('subject_id', $this->subject_id)])
            )
            ->columns([
                Tables\Columns\TextColumn::make('student_number')
                    ->label('Nº')
                    ->sortable(),
                Tables\Columns\TextColumn::make('candidate.full_name')
                    ->label('Nome do Aluno')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextInputColumn::make('frequencia_1')
                    ->label('Freq. 1')
                    ->getStateUsing(fn (Student $record) => $this->getEvaluationScore($record, 'frequencia', 1))
                    ->updateStateUsing(fn (Student $record, $state) => $this->saveEvaluation($record, 'frequencia', 1, $state)),
                Tables\Columns\TextInputColumn::make('frequencia_2')
                    ->label('Freq. 2')
                    ->getStateUsing(fn (Student $record) => $this->getEvaluationScore($record, 'frequencia', 2))
                    ->updateStateUsing(fn (Student $record, $state) => $this->saveEvaluation($record, 'frequencia', 2, $state)),
                Tables\Columns\TextColumn::make('media_frequencia')
                    ->label('Média Freq.')
                    ->getStateUsing(fn (Student $record) => $this->calculateAverage($record, 'frequencia'))
                    ->badge()
                    ->color(fn ($state) => $state !== '-' && floatval($state) >= 10 ? 'success' : 'danger'),
                Tables\Columns\TextInputColumn::make('exame')
                    ->label('Exame')
                    ->getStateUsing(fn (Student $record) => $this->getEvaluationScore($record, 'exame', 1))
                    ->updateStateUsing(fn (Student $record, $state) => $this->saveEvaluation($record, 'exame', 1, $state)),
                Tables\Columns\TextColumn::make('media_final')
                    ->label('Média Final')
                    ->getStateUsing(fn (Student $record) => $this->calculateFinalAverage($record))
                    ->badge()
                    ->color(fn ($state) => $state !== '-' && floatval($state) >= 10 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('resultado')
                    ->label('Resultado')
                    ->getStateUsing(fn (Student $record) => $this->getResultado($record))
                    ->badge()
                    ->color(fn ($state) => $state === 'Aprovado' ? 'success' : 'danger'),
            ])
            ->filters([])
            ->actions([])
            ->bulkActions([])
            ->striped()
            ->emptyStateHeading('Nenhum aluno encontrado')
            ->emptyStateDescription('Verifique se existem alunos matriculados nesta turma.');
    }

    protected function getEvaluationScore(Student $student, string $type, int $order): ?string
    {
        if (!$this->subject_id) return null;
        
        $evaluation = Evaluation::where('student_id', $student->id)
            ->where('subject_id', $this->subject_id)
            ->where('evaluation_type', $type)
            ->where('observations', 'order_' . $order)
            ->first();
        
        return $evaluation?->score;
    }

    protected function saveEvaluation(Student $student, string $type, int $order, $score): void
    {
        if ($score === null || $score === '' || !$this->subject_id || !$this->class_id) {
            return;
        }

        $class = StudentClass::find($this->class_id);

        Evaluation::updateOrCreate(
            [
                'student_id' => $student->id,
                'subject_id' => $this->subject_id,
                'institution_id' => $class?->institution_id,
                'evaluation_type' => $type,
                'observations' => 'order_' . $order,
            ],
            [
                'score' => floatval($score),
                'evaluated_at' => now(),
            ]
        );
    }

    protected function calculateAverage(Student $student, string $type): string
    {
        if (!$this->subject_id) return '-';
        
        $evaluations = Evaluation::where('student_id', $student->id)
            ->where('subject_id', $this->subject_id)
            ->where('evaluation_type', $type)
            ->pluck('score')
            ->filter();
        
        if ($evaluations->isEmpty()) {
            return '-';
        }
        
        return number_format($evaluations->avg(), 1);
    }

    protected function calculateFinalAverage(Student $student): string
    {
        $mediaFreq = $this->calculateAverage($student, 'frequencia');
        $exame = $this->getEvaluationScore($student, 'exame', 1);
        
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

    protected function getResultado(Student $student): string
    {
        $media = $this->calculateFinalAverage($student);
        if ($media === '-') return 'Pendente';
        return floatval($media) >= 10 ? 'Aprovado' : 'Reprovado';
    }

    public function exportPdf()
    {
        if (!$this->class_id || !$this->subject_id) {
            return;
        }
        
        $url = route('pauta.mini-pauta.print', [
            'turma' => $this->class_id,
            'disciplina' => $this->subject_id,
        ]);
        
        return redirect($url);
    }
}
