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
                    ->circular()
                    ->size(40)
                    ->defaultImageUrl(function ($record) {
                        $name = $record->full_name ?? 'Alistado';
                        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=0D47A1&color=fff&size=128&bold=true';
                    }),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nome Completo')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('id_number')
                    ->label('Nº BI')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefone')
                    ->searchable()
                    ->icon('heroicon-o-phone'),
                Tables\Columns\TextColumn::make('gender')
                    ->label('Género')
                    ->badge()
                    ->color(fn ($state) => $state === 'Masculino' ? 'info' : 'danger'),
                Tables\Columns\TextColumn::make('student_type')
                    ->label('Estado')
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data Registo')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ->successNotificationTitle('Alistado criado com sucesso!')
                    ->after(function (\App\Models\Candidate $record) {
                        // Enviar SMS ao alistado após criar
                        $phone = $record->phone;
                        
                        if (!empty($phone)) {
                            $candidateName = $record->full_name ?? 'Alistado';
                            
                            // Buscar nome da instituição
                            $institutionName = 'Escola de Formacao da Policia Nacional';
                            if ($record->institution_id) {
                                $institution = \App\Models\Institution::find($record->institution_id);
                                if ($institution) {
                                    $institutionName = $institution->name;
                                }
                            }
                            
                            // Remover acentos
                            $removeAccents = fn($str) => strtr($str, [
                                'ã' => 'a', 'á' => 'a', 'à' => 'a', 'â' => 'a',
                                'é' => 'e', 'ê' => 'e', 'í' => 'i', 'ó' => 'o',
                                'ô' => 'o', 'õ' => 'o', 'ú' => 'u', 'ç' => 'c',
                                'Ã' => 'A', 'Á' => 'A', 'À' => 'A', 'Â' => 'A',
                                'É' => 'E', 'Ê' => 'E', 'Í' => 'I', 'Ó' => 'O',
                                'Ô' => 'O', 'Õ' => 'O', 'Ú' => 'U', 'Ç' => 'C',
                            ]);
                            
                            $institutionName = $removeAccents($institutionName);
                            $candidateName = $removeAccents($candidateName);
                            
                            try {
                                $smsService = new \App\Services\SmsService();
                                $result = $smsService->sendAgentRegistrationNotification(
                                    $phone,
                                    $candidateName,
                                    $institutionName
                                );
                                
                                if ($result['success']) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('SMS enviado')
                                        ->body("Notificacao enviada para {$phone}")
                                        ->success()
                                        ->send();
                                } else {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Falha ao enviar SMS')
                                        ->body("Nao foi possivel enviar SMS. Detalhes: " . ($result['message'] ?? 'Erro desconhecido'))
                                        ->warning()
                                        ->send();
                                }
                            } catch (\Exception $e) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Erro ao enviar SMS')
                                    ->body("Erro: " . $e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }
                    }),
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

    public static function canAccess(): bool
    {
        return auth()->user()?->can('ViewAny:Candidate') ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}
