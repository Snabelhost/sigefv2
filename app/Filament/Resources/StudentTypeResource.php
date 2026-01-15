<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentTypeResource\Pages;
use App\Models\StudentType;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudentTypeResource extends Resource
{
    protected static ?string $model = StudentType::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-s-tag';
    protected static string|\UnitEnum|null $navigationGroup = 'Configurações';
    protected static ?int $navigationSort = 6;
    protected static ?string $navigationLabel = 'Tipos de Alunos';
    protected static ?string $modelLabel = 'Tipo de Aluno';
    protected static ?string $pluralModelLabel = 'Tipos de Alunos';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Section::make('Informações do Tipo')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome do Tipo')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(191),
                        Forms\Components\TextInput::make('description')
                            ->label('Descrição')
                            ->maxLength(255),
                        Forms\Components\Select::make('color')
                            ->label('Cor do Status')
                            ->options(StudentType::getColorOptions())
                            ->required()
                            ->default('gray')
                            ->helperText('Selecione a cor que simboliza este status'),
                        Forms\Components\TextInput::make('order')
                            ->label('Ordem')
                            ->numeric()
                            ->default(0)
                            ->helperText('Ordem de exibição (menor = primeiro)'),
                    ])->columns(2),
                \Filament\Schemas\Components\Section::make('Configurações de Fase')
                    ->schema([
                        Forms\Components\Toggle::make('has_phase')
                            ->label('Este tipo de aluno possui fase de curso associada?')
                            ->live()
                            ->default(false)
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('phase_name')
                            ->label('Nome da Fase')
                            ->maxLength(50)
                            ->visible(fn ($get): bool => $get('has_phase') === true)
                            ->columnSpan(2),
                    ])->columns(2),
                \Filament\Schemas\Components\Section::make('Estado')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Tipo de Aluno Activo')
                            ->default(true)
                            ->helperText('Desmarque para desactivar este tipo de aluno'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->striped()
            ->defaultSort('order', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->label('#')
                    ->sortable()
                    ->width('60px'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Descrição')
                    ->limit(40)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('color')
                    ->label('Cor')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => StudentType::getColorOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => $state),
                Tables\Columns\IconColumn::make('has_phase')
                    ->label('Fase')
                    ->boolean(),
                Tables\Columns\TextColumn::make('phase_name')
                    ->label('Nome da Fase')
                    ->placeholder('-'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('students_count')
                    ->label('Alunos')
                    ->counts('students')
                    ->badge()
                    ->color('primary'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Activo'),
                Tables\Filters\TernaryFilter::make('has_phase')
                    ->label('Possui Fase'),
            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->modalSubmitAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-check')->label('Criar'))
                    ->modalCancelAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-x-mark')->label('Cancelar')->color('danger'))
                    ->createAnotherAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-plus-circle')->label('Salvar e criar outro'))
                    ->createAnother(true)
                    ->successNotificationTitle('Tipo criado com sucesso!')
                    ->label('Novo Tipo'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil-square')
                    ->modalSubmitAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-check')->label('Salvar'))
                    ->modalCancelAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-x-mark')->label('Cancelar')->color('danger'))
                    ->successNotificationTitle('Tipo atualizado com sucesso!'),
                \Filament\Actions\DeleteAction::make()->icon('heroicon-o-trash'),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('order');
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
            'index' => Pages\ListStudentTypes::route('/'),
        ];
    }
}
