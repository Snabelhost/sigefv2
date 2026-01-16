<?php

namespace App\Filament\Escola\Resources;

use App\Filament\Escola\Resources\CandidateResource\Pages;
use App\Models\Candidate;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CandidateResource extends Resource
{
    protected static ?string $model = Candidate::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-s-user-plus';
    protected static ?string $navigationLabel = 'Alistados';
    protected static ?string $modelLabel = 'Alistado';
    protected static ?string $pluralModelLabel = 'Alistados';

    /**
     * Badge com total de alistados da instituição
     */
    public static function getNavigationBadge(): ?string
    {
        $tenant = \Filament\Facades\Filament::getTenant();
        if ($tenant) {
            return (string) \App\Models\Candidate::where('institution_id', $tenant->id)->count();
        }
        return null;
    }

    /**
     * Cor do badge
     */
    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    protected static ?int $navigationSort = 3;

    public static function form(Schema $form): Schema
    {
        // Usa o formulário partilhado com Wizard
        return $form->schema(\App\Filament\Forms\SharedForms::getCandidateFormSchema());
    }

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
                    ->label('Nome Completo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('id_number')
                    ->label('Nº BI')
                    ->searchable(),
                Tables\Columns\TextColumn::make('recruitmentType.name')
                    ->label('Recrutamento')
                    ->sortable(),
                Tables\Columns\TextColumn::make('gender')
                    ->label('Género'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'info' => 'admitted',
                    ]),
            ])

            ->filters([])
            ->headerActions([
                \Filament\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->mutateFormDataUsing(function (array $data): array {
                        // Definir automaticamente a instituição do utilizador logado
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCandidates::route('/'),
        ];
    }
}
