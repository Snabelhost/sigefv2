<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CoursePhaseResource\Pages;
use App\Filament\Resources\CoursePhaseResource\RelationManagers;
use App\Models\CoursePhase;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CoursePhaseResource extends Resource
{
    protected static ?string $model = CoursePhase::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-s-signal';
    protected static string|\UnitEnum|null $navigationGroup = 'Currículo';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'Fases de Curso';
    protected static ?string $modelLabel = 'Fase de Curso';
    protected static ?string $pluralModelLabel = 'Fases de Curso';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\Select::make('course_id')
                    ->label('Curso')
                    ->relationship('course', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('name')
                    ->label('Nome da Fase')
                    ->required()
                    ->maxLength(191),
                Forms\Components\TextInput::make('order')
                    ->label('Ordem')
                    ->required()
                    ->numeric()
                    ->default(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->striped()
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('course.name')
                    ->label('Curso')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Fase')
                    ->searchable(),
                Tables\Columns\TextColumn::make('order')
                    ->label('Ordem')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
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
                \Filament\Actions\DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->before(function (CoursePhase $record, \Filament\Actions\DeleteAction $action) {
                        // Verificar se há alunos usando esta fase
                        $studentsCount = \App\Models\Student::where('current_phase_id', $record->id)->count();
                        
                        // Verificar se há disciplinas usando esta fase
                        $subjectsCount = \App\Models\Subject::where('course_phase_id', $record->id)->count();
                        
                        // Verificar se há inscrições usando esta fase
                        $enrollmentsCount = \App\Models\StudentClassEnrollment::where('course_phase_id', $record->id)->count() +
                                           \App\Models\StudentSubjectEnrollment::where('course_phase_id', $record->id)->count();
                        
                        if ($studentsCount > 0 || $subjectsCount > 0 || $enrollmentsCount > 0) {
                            $messages = [];
                            if ($studentsCount > 0) $messages[] = "$studentsCount aluno(s)";
                            if ($subjectsCount > 0) $messages[] = "$subjectsCount disciplina(s)";
                            if ($enrollmentsCount > 0) $messages[] = "$enrollmentsCount inscrição(ões)";
                            
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Não é possível excluir')
                                ->body('Esta fase está vinculada a: ' . implode(', ', $messages) . '. Remova as dependências primeiro.')
                                ->persistent()
                                ->send();
                            
                            $action->cancel();
                        }
                    }),
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
            'index' => Pages\ListCoursePhases::route('/'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('ViewAny:CoursePhase') ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}



