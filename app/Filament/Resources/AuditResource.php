<?php

namespace App\Filament\Resources;

use Tapp\FilamentAuditing\Filament\Resources\Audits\AuditResource as BaseAuditResource;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class AuditResource extends BaseAuditResource
{
    protected static ?string $slug = 'audits';

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-shield-check';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Gestão de Acesso';
    }

    public static function getNavigationSort(): ?int
    {
        return 4;
    }

    public static function getModelLabel(): string
    {
        return 'Auditoria';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Auditorias';
    }

    public static function getNavigationLabel(): string
    {
        return 'Auditorias';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('ViewAny:Audit') ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function table(Table $table): Table
    {
        return parent::table($table)
            ->actions([
                \Filament\Actions\ViewAction::make()
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Detalhes da Auditoria')
                    ->modalWidth('4xl'),
                \Filament\Actions\Action::make('restore')
                    ->label('Reverter')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-o-arrow-uturn-left')
                    ->modalIconColor('warning')
                    ->modalHeading('Reverter Alteração')
                    ->modalDescription('Tem a certeza que deseja reverter esta alteração? Os valores antigos serão restaurados.')
                    ->modalSubmitActionLabel('Sim, reverter')
                    ->modalCancelActionLabel('Cancelar')
                    ->extraModalFooterActions([])
                    ->modalSubmitAction(fn (\Filament\Actions\Action $action) => $action
                        ->icon('heroicon-o-check')
                        ->color('primary')
                        ->label('Sim, reverter'))
                    ->modalCancelAction(fn (\Filament\Actions\Action $action) => $action
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->label('Cancelar'))
                    ->visible(fn ($record) => $record->event === 'updated' && !empty($record->old_values))
                    ->action(function ($record) {
                        try {
                            // Obter o modelo original
                            $auditableClass = $record->auditable_type;
                            $auditableId = $record->auditable_id;
                            
                            // Verificar se o modelo ainda existe
                            $model = $auditableClass::find($auditableId);
                            
                            if (!$model) {
                                Notification::make()
                                    ->title('Erro ao reverter')
                                    ->body('O registo original não foi encontrado.')
                                    ->danger()
                                    ->send();
                                return;
                            }
                            
                            // Restaurar os valores antigos
                            $oldValues = $record->old_values;
                            
                            foreach ($oldValues as $key => $value) {
                                $model->{$key} = $value;
                            }
                            
                            $model->save();
                            
                            Notification::make()
                                ->title('Alteração revertida')
                                ->body('Os valores antigos foram restaurados com sucesso.')
                                ->success()
                                ->send();
                                
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Erro ao reverter')
                                ->body('Ocorreu um erro: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\AuditResource\Pages\ListAudits::route('/'),
        ];
    }
}
