<?php

namespace App\Filament\Escola\Resources;

use App\Filament\Escola\Resources\EquipmentAssignmentResource\Pages;
use App\Models\EquipmentAssignment;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EquipmentAssignmentResource extends Resource
{
    protected static ?string $model = EquipmentAssignment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-s-briefcase';
    protected static ?string $modelLabel = 'Atribuição de Meio';
    protected static ?string $pluralModelLabel = 'Atribuição de Meios';
    protected static ?int $navigationSort = 8;
    
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['student.candidate']);
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Section::make('Dados da Atribuição')
                    ->schema([
                        Forms\Components\Select::make('student_id')
                            ->label('Formando')
                            ->relationship('student', 'student_number')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->student_number} - {$record->candidate->full_name}")
                            ->required()
                            ->searchable(),
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
}
