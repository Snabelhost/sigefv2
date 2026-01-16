<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers;
use App\Models\Student;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-s-academic-cap';
    protected static string|\UnitEnum|null $navigationGroup = 'Gestão Escolar';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationLabel = 'Instruendos';
    protected static ?string $modelLabel = 'Instruendo';
    protected static ?string $pluralModelLabel = 'Instruendos';
    
    // Esconder da navegação
    protected static bool $shouldRegisterNavigation = false;

    /**
     * Badge com total de instruendos
     */
    public static function getNavigationBadge(): ?string
    {
        return (string) Student::whereIn('student_type', ['Instruendo', 'Recruta'])->count();
    }

    /**
     * Cor do badge
     */
    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    // Filtrar apenas Instruendos e Recrutas
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('student_type', ['Instruendo', 'Recruta'])
            ->with(['candidate', 'institution', 'currentPhase']);
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
                        Forms\Components\Select::make('institution_id')
                            ->label('Escola')
                            ->relationship('institution', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('course_map_id')
                            ->label('Mapa de Curso')
                            ->relationship('courseMap', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_title)
                            ->required()
                            ->searchable()
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
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('student_number')
                    ->label('Nº Ordem')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('candidate.full_name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('institution.name')
                    ->label('Escola')
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
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->modalSubmitAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-check')->label('Criar'))
                    ->modalCancelAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-x-mark')->label('Cancelar')->color('danger'))
                    ->createAnotherAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-plus-circle')->label('Salvar e criar outro'))
                    ->createAnother(true)
                    ->successNotificationTitle('Registo criado com sucesso!'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil-square')
                    ->modalSubmitAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-check')->label('Salvar'))
                    ->modalCancelAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-x-mark')->label('Cancelar')->color('danger'))
                    ->successNotificationTitle('Registo atualizado com sucesso!'),
                \Filament\Actions\DeleteAction::make()->icon('heroicon-o-trash'),
                \Filament\Actions\Action::make('guiaMarcha')
                    ->label('Guia de Marcha')
                    ->icon('heroicon-s-document-arrow-down')
                    ->color('success')
                    ->url(fn ($record) => route('reports.march-guide', $record))
                    ->openUrlInNewTab(),
                \Filament\Actions\Action::make('historico')
                    ->label('Histórico')
                    ->icon('heroicon-s-clipboard-document-list')
                    ->color('info')
                    ->url(fn ($record) => route('reports.student-history', $record))
                    ->openUrlInNewTab(),
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
