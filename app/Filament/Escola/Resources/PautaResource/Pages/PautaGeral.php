<?php

namespace App\Filament\Escola\Resources\PautaResource\Pages;

use App\Filament\Escola\Resources\PautaResource;
use App\Models\StudentClass;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Evaluation;
use Filament\Resources\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;

class PautaGeral extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string $resource = PautaResource::class;

    public StudentClass $record;
    public array $subjects = [];

    public function getView(): string
    {
        return 'filament.escola.resources.pauta-resource.pages.pauta-geral';
    }

    public function mount(StudentClass $record): void
    {
        $this->record = $record;
        $this->subjects = Subject::where('institution_id', Filament::getTenant()?->id)
            ->pluck('name', 'id')
            ->toArray();
    }

    public function getTitle(): string
    {
        return 'Pauta Geral - ' . $this->record->name;
    }

    public function getBreadcrumbs(): array
    {
        return [
            PautaResource::getUrl() => 'Pautas',
            '#' => 'Pauta Geral - ' . $this->record->name,
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
        $subjects = Subject::where('institution_id', Filament::getTenant()?->id)->get();
        
        $columns = [
            Tables\Columns\TextColumn::make('student_number')
                ->label('Nº Ordem')
                ->sortable(),
            Tables\Columns\TextColumn::make('candidate.full_name')
                ->label('Nome do Aluno')
                ->searchable()
                ->sortable(),
        ];

        // Adicionar coluna de média para cada disciplina dinamicamente
        foreach ($subjects as $subject) {
            $columns[] = Tables\Columns\TextColumn::make('media_' . $subject->id)
                ->label(substr($subject->name, 0, 8))
                ->getStateUsing(fn (Student $record) => $this->getSubjectFinalAverage($record, $subject->id))
                ->badge()
                ->color(fn ($state) => $state !== '-' && floatval($state) >= 10 ? 'success' : ($state === '-' ? 'gray' : 'danger'));
        }

        // Adicionar colunas finais
        $columns[] = Tables\Columns\TextColumn::make('media_geral')
            ->label('Média Geral')
            ->getStateUsing(fn (Student $record) => $this->calculateGeneralAverage($record))
            ->badge()
            ->color(fn ($state) => $state !== '-' && floatval($state) >= 10 ? 'success' : ($state === '-' ? 'gray' : 'danger'));

        $columns[] = Tables\Columns\TextColumn::make('resultado_final')
            ->label('Resultado')
            ->getStateUsing(fn (Student $record) => $this->getResult($record))
            ->badge()
            ->color(fn ($state) => $state === 'Aprovado' ? 'success' : ($state === 'Reprovado' ? 'danger' : 'gray'));

        return $table
            ->query(
                Student::query()
                    ->whereHas('classes', fn (Builder $query) => $query->where('classes.id', $this->record->id))
                    ->with(['candidate', 'evaluations'])
            )
            ->columns($columns)
            ->filters([])
            ->actions([])
            ->bulkActions([]);
    }

    protected function getSubjectFinalAverage(Student $student, int $subjectId): string
    {
        $evaluations = Evaluation::where('student_id', $student->id)
            ->where('subject_id', $subjectId)
            ->pluck('score')
            ->filter();
        
        if ($evaluations->isEmpty()) {
            return '-';
        }
        
        return number_format($evaluations->avg(), 1);
    }

    protected function calculateGeneralAverage(Student $student): string
    {
        $subjects = Subject::where('institution_id', Filament::getTenant()?->id)->get();
        $averages = [];

        foreach ($subjects as $subject) {
            $avg = $this->getSubjectFinalAverage($student, $subject->id);
            if ($avg !== '-') {
                $averages[] = floatval($avg);
            }
        }

        if (empty($averages)) {
            return '-';
        }

        return number_format(array_sum($averages) / count($averages), 1);
    }

    protected function getResult(Student $student): string
    {
        $avg = $this->calculateGeneralAverage($student);
        
        if ($avg === '-') {
            return 'Pendente';
        }
        
        return floatval($avg) >= 10 ? 'Aprovado' : 'Reprovado';
    }

    public function exportPdf()
    {
        // Redirecionar para página de impressão
        $url = route('pauta.pauta-geral.print', [
            'turma' => $this->record->id,
        ]);
        
        return redirect($url);
    }
}
