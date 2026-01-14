<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InstitutionResource\Pages;
use App\Filament\Resources\InstitutionResource\RelationManagers;
use App\Models\Institution;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InstitutionResource extends Resource
{
    protected static ?string $model = Institution::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-s-building-library';
    protected static string|\UnitEnum|null $navigationGroup = 'Instituições';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = 'Instituições';
    protected static ?string $modelLabel = 'Instituição';
    protected static ?string $pluralModelLabel = 'Instituições';
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['type']);
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\Select::make('institution_type_id')
                    ->label('Tipo de Instituição')
                    ->relationship('type', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(191),
                Forms\Components\TextInput::make('acronym')
                    ->label('Sigla')
                    ->maxLength(191),
                Forms\Components\TextInput::make('phone')
                    ->label('Telefone')
                    ->tel()
                    ->maxLength(191),
                Forms\Components\TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->maxLength(191),
                Forms\Components\TextInput::make('country')
                    ->label('País')
                    ->required()
                    ->maxLength(191)
                    ->default('Angola'),
                Forms\Components\TextInput::make('province')
                    ->label('Província')
                    ->maxLength(191),
                Forms\Components\TextInput::make('municipality')
                    ->label('Município')
                    ->maxLength(191),
                Forms\Components\Textarea::make('address')
                    ->label('Endereço')
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('logo')
                    ->label('Logótipo')
                    ->image()
                    ->directory('institutions'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('type.name')
                    ->label('Tipo')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('acronym')
                    ->label('Sigla')
                    ->searchable(),
                Tables\Columns\TextColumn::make('province')
                    ->label('Província')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
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
                \Filament\Actions\EditAction::make()->icon('heroicon-o-pencil-square'),
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
            'index' => Pages\ListInstitutions::route('/'),
        ];
    }
}



