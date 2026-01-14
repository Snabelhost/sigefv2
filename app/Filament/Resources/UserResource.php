<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-s-users';
    protected static string|\UnitEnum|null $navigationGroup = 'Gestão de Acesso';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Utilizador';
    protected static ?string $pluralModelLabel = 'Utilizadores';
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['roles', 'institution']);
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Section::make('Informação da Conta')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome Completo')
                            ->required()
                            ->maxLength(191),
                        Forms\Components\TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(191),
                        Forms\Components\TextInput::make('password')
                            ->label('Palavra-passe')
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->maxLength(191),
                        Forms\Components\TextInput::make('phone')
                            ->label('Telefone')
                            ->tel()
                            ->maxLength(191),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Permissões e Acesso')
                    ->schema([
                        Forms\Components\Select::make('institution_id')
                            ->label('Instituição/Escola')
                            ->relationship('institution', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Seleccione a escola para utilizadores do painel Escola'),
                        Forms\Components\Select::make('roles')
                            ->label('Papéis (Roles)')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Conta Activa')
                            ->default(true)
                            ->required(),
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
                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),
                Tables\Columns\TextColumn::make('institution.name')
                    ->label('Instituição')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Papéis')
                    ->badge()
                    ->separator(','),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
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
            'index' => Pages\ListUsers::route('/'),
        ];
    }
}
