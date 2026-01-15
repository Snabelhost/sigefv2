<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CertificadoResource\Pages;
use App\Models\Student;
use App\Models\StudentClass;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CertificadoResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-s-academic-cap';
    protected static string|\UnitEnum|null $navigationGroup = 'Gestão Escolar';
    protected static ?string $navigationLabel = 'Certificados';
    protected static ?string $modelLabel = 'Certificado';
    protected static ?string $pluralModelLabel = 'Certificados';
    protected static ?int $navigationSort = 11;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        // Apenas alunos aprovados (com média >= 10)
        return parent::getEloquentQuery()
            ->with(['candidate', 'classes.institution', 'classes.courseMap.course', 'evaluations'])
            ->whereHas('evaluations');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->striped()
            ->defaultSort('student_number', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('student_number')
                    ->label('Nº Ordem')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('candidate.full_name')
                    ->label('Nome do Aluno')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cia')
                    ->label('CIA')
                    ->sortable(),
                Tables\Columns\TextColumn::make('platoon')
                    ->label('Pelotão')
                    ->sortable(),
                Tables\Columns\TextColumn::make('section')
                    ->label('Secção')
                    ->sortable(),
                Tables\Columns\TextColumn::make('classes.name')
                    ->label('Turma')
                    ->sortable(),
                Tables\Columns\TextColumn::make('media_geral')
                    ->label('Média Geral')
                    ->getStateUsing(fn (Student $record) => static::calculateGeneralAverage($record))
                    ->badge()
                    ->color(fn ($state) => $state !== '-' && floatval($state) >= 10 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('resultado')
                    ->label('Resultado')
                    ->getStateUsing(fn (Student $record) => static::getResult($record))
                    ->badge()
                    ->color(fn ($state) => $state === 'Aprovado' ? 'success' : 'danger'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('cia')
                    ->label('CIA')
                    ->options(fn () => Student::whereNotNull('cia')->distinct()->pluck('cia', 'cia')->toArray()),
                Tables\Filters\SelectFilter::make('platoon')
                    ->label('Pelotão')
                    ->options(fn () => Student::whereNotNull('platoon')->distinct()->pluck('platoon', 'platoon')->toArray()),
                Tables\Filters\SelectFilter::make('section')
                    ->label('Secção')
                    ->options(fn () => Student::whereNotNull('section')->distinct()->pluck('section', 'section')->toArray()),
                Tables\Filters\SelectFilter::make('class_id')
                    ->label('Turma')
                    ->relationship('classes', 'name'),
            ])
            ->headerActions([
                \Filament\Actions\Action::make('gerarCertificados')
                    ->label('Gerar Certificados')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('primary')
                    ->url(fn () => route('certificados.gerar'))
                    ->openUrlInNewTab(),
            ])
            ->actions([
                \Filament\Actions\Action::make('gerarCertificado')
                    ->label('Certificado')
                    ->icon('heroicon-o-document-text')
                    ->color('primary')
                    ->url(fn (Student $record) => route('certificados.individual', ['student' => $record->id]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([]);
    }

    protected static function calculateGeneralAverage(Student $student): string
    {
        $evaluations = $student->evaluations;
        
        if ($evaluations->isEmpty()) {
            return '-';
        }
        
        return number_format($evaluations->avg('score'), 1);
    }

    protected static function getResult(Student $student): string
    {
        $avg = static::calculateGeneralAverage($student);
        
        if ($avg === '-') {
            return 'Pendente';
        }
        
        return floatval($avg) >= 10 ? 'Aprovado' : 'Reprovado';
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCertificados::route('/'),
        ];
    }
}
