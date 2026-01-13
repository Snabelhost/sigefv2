<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EvaluationResource\Pages;
use App\Filament\Resources\EvaluationResource\RelationManagers;
use App\Models\Evaluation;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EvaluationResource extends Resource
{
    protected static ?string $model = Evaluation::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-s-chart-bar-square';
    protected static string|\UnitEnum|null $navigationGroup = 'Gestão Escolar';
    protected static ?string $modelLabel = 'Avaliação';
    protected static ?string $pluralModelLabel = 'Avaliações';
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['student.candidate', 'subject', 'trainer', 'coursePhase']);
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Section::make('Dados da Avaliação')
                    ->schema([
                        Forms\Components\Select::make('student_id')
                            ->label('Formando')
                            ->relationship('student', 'student_number')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->student_number} - {$record->candidate->full_name}")
                            ->required()
                            ->searchable(),
                        Forms\Components\Select::make('subject_id')
                            ->label('Disciplina')
                            ->relationship('subject', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('course_phase_id')
                            ->label('Fase do Curso')
                            ->relationship('coursePhase', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('evaluation_type')
                            ->label('Tipo de Avaliação')
                            ->options([
                                'frequencia' => 'Frequência',
                                'exame' => 'Exame',
                                'pratico' => 'Prático',
                                'comportamental' => 'Comportamental',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('score')
                            ->label('Nota/Valor')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(20),
                        Forms\Components\Select::make('evaluated_by')
                            ->label('Avaliador (Formador)')
                            ->relationship('trainer', 'full_name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\DateTimePicker::make('evaluated_at')
                            ->label('Data e Hora')
                            ->required()
                            ->default(now()),
                        Forms\Components\Textarea::make('observations')
                            ->label('Observações')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('student.student_number')
                    ->label('Nº Ordem')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('student.candidate.full_name')
                    ->label('Formando')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Disciplina')
                    ->sortable(),
                Tables\Columns\TextColumn::make('evaluation_type')
                    ->label('Tipo')
                    ->badge(),
                Tables\Columns\TextColumn::make('score')
                    ->label('Nota')
                    ->numeric()
                    ->sortable()
                    ->color(fn (string $state): string => $state < 10 ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('trainer.full_name')
                    ->label('Avaliador')
                    ->sortable(),
                Tables\Columns\TextColumn::make('evaluated_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make()->icon('heroicon-o-plus'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvaluations::route('/'),
        ];
    }
}
