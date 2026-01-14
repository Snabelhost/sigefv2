<?php

namespace App\Filament\Escola\Resources;

use App\Filament\Escola\Resources\SelectionTestResource\Pages;
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
    protected static ?string $navigationLabel = 'Provas de Seleção';
    protected static ?string $modelLabel = 'Prova de Seleção';
    protected static ?string $pluralModelLabel = 'Provas de Seleção';
    protected static ?int $navigationSort = 10;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Section::make('Informação da Prova')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(191),
                        Forms\Components\Select::make('type')
                            ->label('Tipo')
                            ->options([
                                'escrita' => 'Escrita',
                                'pratica' => 'Prática',
                                'fisica' => 'Física',
                                'medica' => 'Médica',
                                'psicotecnica' => 'Psicotécnica',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('order')
                            ->label('Ordem')
                            ->numeric()
                            ->default(0),
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
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo'),
                Tables\Columns\TextColumn::make('order')
                    ->label('Ordem')
                    ->sortable(),
            ])

            ->filters([])
            ->actions([
                \Filament\Actions\EditAction::make()->icon('heroicon-o-pencil-square'),
                \Filament\Actions\DeleteAction::make()->icon('heroicon-o-trash'),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSelectionTests::route('/'),
        ];
    }
}
