<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InstitutionTypeResource\Pages;
use App\Filament\Resources\InstitutionTypeResource\RelationManagers;
use App\Models\InstitutionType;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InstitutionTypeResource extends Resource
{
    protected static ?string $model = InstitutionType::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-s-squares-2x2';
    protected static string|\UnitEnum|null $navigationGroup = 'Configurações';
    protected static ?string $navigationLabel = 'Tipos de Instituição';
    protected static ?string $modelLabel = 'Tipo de Instituição';
    protected static ?string $pluralModelLabel = 'Tipos de Instituição';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(191),
                Forms\Components\Textarea::make('description')
                    ->label('Descrição')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
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
            'index' => Pages\ListInstitutionTypes::route('/'),
            'create' => Pages\CreateInstitutionType::route('/create'),
            'edit' => Pages\EditInstitutionType::route('/{record}/edit'),
        ];
    }
}
