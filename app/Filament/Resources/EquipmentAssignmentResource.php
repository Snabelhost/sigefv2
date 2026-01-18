<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EquipmentAssignmentResource\Pages;
use App\Filament\Resources\EquipmentAssignmentResource\RelationManagers;
use App\Models\EquipmentAssignment;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EquipmentAssignmentResource extends Resource
{
    protected static ?string $model = EquipmentAssignment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-s-cube';
    protected static string|\UnitEnum|null $navigationGroup = 'Gestão Escolar';
    protected static ?int $navigationSort = 7;
    protected static ?string $modelLabel = 'Atribuição de Meio';
    protected static ?string $pluralModelLabel = 'Atribuição de Meios';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Section::make('Dados da Atribuição')
                    ->schema([
                        Forms\Components\Select::make('student_id')
                            ->label('Formando')
                            ->options(function () {
                                // IDs dos estudantes que já têm meios atribuídos
                                $assignedStudentIds = EquipmentAssignment::distinct()->pluck('student_id');
                                
                                // Apenas estudantes inscritos em turmas e sem meios atribuídos
                                return \App\Models\Student::with(['candidate', 'classEnrollments'])
                                    ->whereHas('classEnrollments')
                                    ->whereNotIn('id', $assignedStudentIds)
                                    ->get()
                                    ->mapWithKeys(fn ($s) => [
                                        $s->id => "{$s->student_number} - " . ($s->candidate?->full_name ?? 'N/A') . " ({$s->student_type})"
                                    ]);
                            })
                            ->required()
                            ->searchable()
                            ->helperText('Apenas formandos inscritos em turmas sem meios atribuídos'),
                        Forms\Components\TextInput::make('equipment_name')
                            ->label('Nome do Equipamento/Meio')
                            ->required()
                            ->maxLength(191),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Quantidade')
                            ->required()
                            ->numeric()
                            ->default(1),
                        Forms\Components\DateTimePicker::make('assigned_at')
                            ->label('Data de Atribuição')
                            ->required()
                            ->default(now()),
                        Forms\Components\DateTimePicker::make('returned_at')
                            ->label('Data de Devolução'),
                        Forms\Components\TextInput::make('condition')
                            ->label('Estado/Condição')
                            ->placeholder('Ex: Novo, Usado, Danificado')
                            ->maxLength(191),
                        Forms\Components\Select::make('assigned_by')
                            ->label('Atribuído por')
                            ->relationship('assigner', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])->columns(2)->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->striped()
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('student.student_number')
                    ->label('Nº Ordem')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('student.candidate.full_name')
                    ->label('Formando')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('equipment_name')
                    ->label('Meio')
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Qtd')
                    ->sortable(),
                Tables\Columns\TextColumn::make('assigned_at')
                    ->label('Atribuição')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('returned_at')
                    ->label('Devolução')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Ainda com o formando'),
                Tables\Columns\TextColumn::make('condition')
                    ->label('Condição'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('institution_id')
                    ->label('Instituição')
                    ->relationship('institution', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('condition')
                    ->label('Condição')
                    ->options([
                        'Novo' => 'Novo',
                        'Usado' => 'Usado',
                        'Danificado' => 'Danificado',
                    ]),
                Tables\Filters\TernaryFilter::make('devolvido')
                    ->label('Estado de Devolução')
                    ->placeholder('Todos')
                    ->trueLabel('Devolvido')
                    ->falseLabel('Ainda com o formando')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('returned_at'),
                        false: fn (Builder $query) => $query->whereNull('returned_at'),
                    ),
            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->label(function () {
                        // Contar formandos inscritos sem meios atribuídos
                        $assignedStudentIds = EquipmentAssignment::distinct()->pluck('student_id');
                        $pendingCount = \App\Models\Student::whereHas('classEnrollments')
                            ->whereNotIn('id', $assignedStudentIds)
                            ->count();
                        return "Criar Atribuição de Meio ($pendingCount pendentes)";
                    })
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
            'index' => Pages\ListEquipmentAssignments::route('/'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('ViewAny:EquipmentAssignment') ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}



