<?php

namespace App\Filament\Resources\Shield;

use BezhanSalleh\FilamentShield\Resources\Roles\RoleResource as BaseRoleResource;
use App\Filament\Resources\Shield\RoleResource\Pages;
use Spatie\Permission\Models\Role;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Str;

class RoleResource extends BaseRoleResource
{
    protected static ?int $navigationSort = 2;
    
    /**
     * Retorna o badge com o número de funções
     */
    public static function getNavigationBadge(): ?string
    {
        return (string) Role::count();
    }

    /**
     * Cor do badge
     */
    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    /**
     * Sobrescreve a tabela para adicionar o botão Criar no header
     */
    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->striped()
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->weight(FontWeight::Medium)
                    ->label(__('filament-shield::filament-shield.column.name'))
                    ->formatStateUsing(fn (string $state): string => Str::headline($state))
                    ->searchable(),
                Tables\Columns\TextColumn::make('guard_name')
                    ->badge()
                    ->color('warning')
                    ->label(__('filament-shield::filament-shield.column.guard_name')),
                Tables\Columns\TextColumn::make('permissions_count')
                    ->badge()
                    ->label(__('filament-shield::filament-shield.column.permissions'))
                    ->counts('permissions')
                    ->color('primary'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('filament-shield::filament-shield.column.updated_at'))
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->label('Criar Função')
                    ->url(fn () => static::getUrl('create')),
            ])
            ->actions([
                \Filament\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil-square'),
                \Filament\Actions\DeleteAction::make()
                    ->icon('heroicon-o-trash'),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Sobrescreve as páginas para usar nossa página de edição personalizada
     * que força a limpeza do cache de permissões.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'view' => \BezhanSalleh\FilamentShield\Resources\Roles\Pages\ViewRole::route('/{record}'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
