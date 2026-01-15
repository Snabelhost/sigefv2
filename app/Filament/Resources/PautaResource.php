<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PautaResource\Pages;
use App\Models\StudentClass;
use App\Models\Subject;
use App\Models\Student;
use App\Models\Evaluation;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PautaResource extends Resource
{
    protected static ?string $model = StudentClass::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-s-clipboard-document-list';
    protected static string|\UnitEnum|null $navigationGroup = 'Gestão Escolar';
    protected static ?string $navigationLabel = 'Pautas';
    protected static ?string $modelLabel = 'Pauta';
    protected static ?string $pluralModelLabel = 'Pautas';
    protected static ?int $navigationSort = 12;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['courseMap.course', 'academicYear', 'students', 'institution']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->striped()
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('institution.name')
                    ->label('Instituição')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Turma')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('courseMap.course.name')
                    ->label('Curso')
                    ->sortable(),
                Tables\Columns\TextColumn::make('academicYear.year')
                    ->label('Ano Académico')
                    ->sortable(),
                Tables\Columns\TextColumn::make('students_count')
                    ->label('Nº Alunos')
                    ->counts('students')
                    ->badge()
                    ->color('primary'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('institution_id')
                    ->label('Instituição')
                    ->relationship('institution', 'name'),
            ])
            ->headerActions([
                \Filament\Actions\Action::make('miniPauta')
                    ->label('Mini Pauta')
                    ->icon('heroicon-o-document-text')
                    ->color('primary')
                    ->url(fn () => static::getUrl('mini-pauta')),
            ])
            ->actions([
                \Filament\Actions\Action::make('pautaGeral')
                    ->label('Pauta Geral')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('success')
                    ->url(fn (StudentClass $record) => static::getUrl('pauta-geral', ['record' => $record])),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPautas::route('/'),
            'mini-pauta' => Pages\MiniPauta::route('/mini-pauta'),
            'pauta-geral' => Pages\PautaGeral::route('/{record}/pauta-geral'),
        ];
    }
}
