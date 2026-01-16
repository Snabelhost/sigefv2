<?php

namespace App\Filament\Escola\Resources;

use App\Filament\Escola\Resources\TrainerResource\Pages;
use App\Models\Trainer;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TrainerResource extends Resource
{
    protected static ?string $model = Trainer::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-s-user-group';
    protected static ?string $modelLabel = 'Formador';
    protected static ?string $pluralModelLabel = 'Formadores';
    protected static ?int $navigationSort = 1;
    
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery()->with(['rank']);
        
        // Filtrar pela instituição do tenant
        $tenant = \Filament\Facades\Filament::getTenant();
        if ($tenant) {
            $query->where('institution_id', $tenant->id);
        }
        
        return $query;
    }

    // Reutiliza o mesmo form do Admin
    public static function form(Schema $form): Schema
    {
        // Usa o formulário partilhado
        return $form->schema(\App\Filament\Forms\SharedForms::getTrainerFormSchema());
    }

    // Reutiliza o mesmo table do Admin (sem coluna de instituição)
    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->striped()
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('photo')
                    ->label('Foto')
                    ->circular(),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable(),
                Tables\Columns\TextColumn::make('rank.name')
                    ->label('Patente')
                    ->sortable(),
                Tables\Columns\TextColumn::make('trainer_type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'interno',
                        'success' => 'externo',
                    ]),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->mutateFormDataUsing(function (array $data): array {
                        $tenant = \Filament\Facades\Filament::getTenant();
                        if ($tenant) {
                            $data['institution_id'] = $tenant->id;
                        }
                        return $data;
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
            'index' => Pages\ListTrainers::route('/'),
        ];
    }
}
