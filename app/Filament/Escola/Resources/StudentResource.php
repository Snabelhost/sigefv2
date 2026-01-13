<?php

namespace App\Filament\Escola\Resources;

use App\Filament\Escola\Resources\StudentResource\Pages;
use App\Models\Student;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-s-academic-cap';
    protected static ?string $modelLabel = 'Formando';
    protected static ?string $pluralModelLabel = 'Formandos';
    
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['candidate', 'currentPhase', 'courseMap']);
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Section::make('Informação Académica')
                    ->schema([
                        Forms\Components\Select::make('candidate_id')
                            ->label('Candidato')
                            ->relationship('candidate', 'full_name')
                            ->required()
                            ->searchable(),
                        Forms\Components\Select::make('course_map_id')
                            ->label('Mapa de Curso')
                            ->relationship('courseMap', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_title)
                            ->required()
                            ->preload(),
                        Forms\Components\TextInput::make('student_number')
                            ->label('Nº de Ordem')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(191),
                        Forms\Components\Select::make('student_type')
                            ->label('Tipo de Formando')
                            ->options([
                                'cadete' => 'Cadete',
                                'subalterno' => 'Subalterno',
                                'sargento' => 'Sargento',
                                'praça' => 'Praça',
                            ])
                            ->required(),
                        Forms\Components\DatePicker::make('enrollment_date')
                            ->label('Data de Matrícula')
                            ->required()
                            ->default(now()),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Estado e Unidade')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'alistado' => 'Alistado',
                                'frequenta' => 'Frequenta',
                                'concluiu' => 'Concluiu',
                                'desistiu' => 'Desistiu',
                                'expulso' => 'Expulso',
                                'falecido' => 'Falecido',
                            ])
                            ->required()
                            ->default('alistado'),
                        Forms\Components\TextInput::make('nuri')
                            ->label('NURI')
                            ->maxLength(191),
                        Forms\Components\TextInput::make('cia')
                            ->label('Companhia')
                            ->maxLength(191),
                        Forms\Components\TextInput::make('platoon')
                            ->label('Pelotão')
                            ->maxLength(191),
                        Forms\Components\TextInput::make('section')
                            ->label('Secção')
                            ->maxLength(191),
                        Forms\Components\Select::make('current_phase_id')
                            ->label('Fase Actual')
                            ->relationship('currentPhase', 'name')
                            ->preload(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('student_number')
                    ->label('Nº Ordem')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('candidate.full_name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('student_type')
                    ->label('Tipo'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'alistado',
                        'primary' => 'frequenta',
                        'success' => 'concluiu',
                        'danger' => ['desistiu', 'expulso', 'falecido'],
                    ]),
                Tables\Columns\TextColumn::make('cia')
                    ->label('CIA'),
                Tables\Columns\TextColumn::make('enrollment_date')
                    ->label('Matrícula')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
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
            'index' => Pages\ListStudents::route('/'),
        ];
    }
}
