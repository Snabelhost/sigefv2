<?php

namespace App\Filament\Dpq\Resources;

use App\Filament\Dpq\Resources\SelectionTestResource\Pages;
use App\Models\SelectionTest;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SelectionTestResource extends Resource
{
    protected static ?string $model = SelectionTest::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-s-clipboard-document-check';
    protected static ?string $modelLabel = 'Teste de Seleção';
    protected static ?string $pluralModelLabel = 'Testes de Seleção';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Section::make('Dados do Teste')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome do Teste')
                            ->required()
                            ->maxLength(191),
                        Forms\Components\Select::make('test_type')
                            ->label('Tipo de Teste')
                            ->options([
                                'fisico' => 'Físico',
                                'psicologico' => 'Psicológico',
                                'medico' => 'Médico',
                                'escrito' => 'Escrito',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('max_score')
                            ->label('Pontuação Máxima')
                            ->numeric()
                            ->default(100),
                        Forms\Components\TextInput::make('passing_score')
                            ->label('Pontuação Mínima')
                            ->numeric()
                            ->default(50),
                        Forms\Components\Textarea::make('description')
                            ->label('Descrição')
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('test_type')
                    ->label('Tipo'),
                Tables\Columns\TextColumn::make('max_score')
                    ->label('Pontuação Máx.'),
                Tables\Columns\TextColumn::make('passing_score')
                    ->label('Pontuação Mín.'),
            ])
            ->filters([
                //
            ])
            ->actions([
                \Filament\Actions\EditAction::make()->icon('heroicon-o-pencil-square')
                    ->label('Editar'),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make()
                        ->label('Eliminar'),
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
            'index' => Pages\ListSelectionTests::route('/'),
        ];
    }
}
