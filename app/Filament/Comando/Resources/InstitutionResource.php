<?php

namespace App\Filament\Comando\Resources;

use App\Filament\Comando\Resources\InstitutionResource\Pages;
use App\Models\Institution;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InstitutionResource extends Resource
{
    protected static ?string $model = Institution::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-s-building-office';
    protected static ?string $modelLabel = 'Instituição';
    protected static ?string $pluralModelLabel = 'Instituições';
    
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['institutionType']);
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Section::make('Dados da Instituição')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(191),
                        Forms\Components\TextInput::make('code')
                            ->label('Código')
                            ->maxLength(50),
                        Forms\Components\Select::make('institution_type_id')
                            ->label('Tipo de Instituição')
                            ->relationship('institutionType', 'name')
                            ->preload(),
                        Forms\Components\Textarea::make('address')
                            ->label('Endereço')
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
                Tables\Columns\TextColumn::make('code')
                    ->label('Código')
                    ->searchable(),
                Tables\Columns\TextColumn::make('institutionType.name')
                    ->label('Tipo'),
            ])
            ->filters([
                //
            ])
            ->actions([
                \Filament\Actions\EditAction::make()
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
            'index' => Pages\ListInstitutions::route('/'),
        ];
    }
}
