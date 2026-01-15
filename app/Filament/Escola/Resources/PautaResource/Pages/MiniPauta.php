<?php

namespace App\Filament\Escola\Resources\PautaResource\Pages;

use App\Filament\Escola\Resources\PautaResource;
use App\Models\StudentClass;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Evaluation;
use Filament\Resources\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;

class MiniPauta extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string $resource = PautaResource::class;

    public StudentClass $record;
    public ?int $subject_id = null;

    public function getView(): string
    {
        return 'filament.escola.resources.pauta-resource.pages.mini-pauta';
    }

    public function mount(StudentClass $record): void
    {
        $this->record = $record;
        
        // Selecionar primeira disciplina disponível
        $firstSubject = Subject::where('institution_id', Filament::getTenant()?->id)->first();
        $this->subject_id = $firstSubject?->id;
    }

    public function getTitle(): string
    {
        return 'Mini Pauta - ' . $this->record->name;
    }

    public function getBreadcrumbs(): array
    {
        return [
            PautaResource::getUrl() => 'Pautas',
            '#' => 'Mini Pauta - ' . $this->record->name,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportPdf')
                ->label('Exportar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(fn () => $this->exportPdf()),
            Action::make('voltar')
                ->label('Voltar')
                ->icon('heroicon-o-arrow-left')
                ->url(PautaResource::getUrl())
                ->color('gray'),
        ];
    }

    public function table(Table $table): Table
    {
        $subjectId = $this->subject_id;
        
        return $table
            ->query(
                Student::query()
                    ->whereHas('classes', fn (Builder $query) => $query->where('classes.id', $this->record->id))
                    ->with(['candidate', 'evaluations' => fn ($q) => $q->where('subject_id', $subjectId)])
            )
            ->columns([
                Tables\Columns\TextColumn::make('student_number')
                    ->label('Nº Ordem')
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
                    ->color(fn ($state) => $state >= 10 ? 'success' : 'danger'),
                Tables\Columns\TextInputColumn::make('exame')
                    ->label('Exame')
                    ->getStateUsing(fn (Student $record) => $this->getEvaluationScore($record, 'exame', 1))
                    ->updateStateUsing(fn (Student $record, $state) => $this->saveEvaluation($record, 'exame', 1, $state)),
                Tables\Columns\TextColumn::make('media_final')
                    ->label('Média Final')
                    ->getStateUsing(fn (Student $record) => $this->calculateFinalAverage($record))
                    ->badge()
                    ->color(fn ($state) => $state >= 10 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('resultado')
                    ->label('Resultado')
                    ->getStateUsing(fn (Student $record) => $this->calculateFinalAverage($record) >= 10 ? 'Aprovado' : 'Reprovado')
                    ->badge()
                    ->color(fn ($state) => $state === 'Aprovado' ? 'success' : 'danger'),
            ])
            ->filters([])
            ->actions([])
            ->bulkActions([]);
    }

    protected function getEvaluationScore(Student $student, string $type, int $order): ?string
    {
        $evaluation = Evaluation::where('student_id', $student->id)
            ->where('subject_id', $this->subject_id)
            ->where('evaluation_type', $type)
            ->where('observations', 'order_' . $order)
            ->first();
        
        return $evaluation?->score;
    }

    protected function saveEvaluation(Student $student, string $type, int $order, $score): void
    {
        if ($score === null || $score === '') {
            return;
        }

        Evaluation::updateOrCreate(
            [
                'student_id' => $student->id,
                'subject_id' => $this->subject_id,
                'institution_id' => Filament::getTenant()?->id,
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
        
        // Média Final = (Média Frequência * 0.4) + (Exame * 0.6)
        if ($mediaFreqValue > 0 && $exameValue > 0) {
            $mediaFinal = ($mediaFreqValue * 0.4) + ($exameValue * 0.6);
            return number_format($mediaFinal, 1);
        }
        
        return '-';
    }

    public function exportPdf()
    {
        // Redirecionar para página de impressão
        $url = route('pauta.mini-pauta.print', [
            'turma' => $this->record->id,
            'disciplina' => $this->subject_id,
        ]);
        
        return redirect($url);
    }
}
