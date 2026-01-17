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
    protected static ?string $navigationLabel = 'Utilizadores';

    /**
     * Badge com total de utilizadores
     */
    public static function getNavigationBadge(): ?string
    {
        return (string) User::count();
    }

    /**
     * Cor do badge
     */
    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['roles', 'institution']);
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Section::make('Dados Pessoais')
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
                        Forms\Components\TextInput::make('phone')
                            ->label('Telefone')
                            ->tel()
                            ->maxLength(191),
                    ])->columns(3)->columnSpanFull(),

                \Filament\Schemas\Components\Section::make('Segurança')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label('Palavra-passe')
                            ->password()
                            ->revealable()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->maxLength(191),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Confirmar Palavra-passe')
                            ->password()
                            ->revealable()
                            ->same('password')
                            ->requiredWith('password')
                            ->maxLength(191),
                    ])->columns(2)->columnSpanFull(),

                \Filament\Schemas\Components\Section::make('Permissões')
                    ->schema([
                        Forms\Components\Select::make('institution_id')
                            ->label('Instituição/Escola')
                            ->relationship('institution', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Seleccione a escola para utilizadores do painel Escola')
                            ->columnSpan(1),
                        Forms\Components\Select::make('roles')
                            ->label('Papéis (Roles)')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->columnSpan(1),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Conta Activa')
                            ->default(true)
                            ->required()
                            ->inline(false),
                    ])->columns(2)->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->striped()
            ->defaultSort('created_at', 'desc')
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
                    ->color(fn (string $state): string => match ($state) {
                        'super_admin' => 'danger',
                        'admin' => 'warning',
                        'escola_admin' => 'success',
                        'dpq_admin' => 'info',
                        'comando_admin' => 'primary',
                        'panel_user' => 'gray',
                        'escola_user' => 'success',
                        'dpq_user' => 'info',
                        'comando_user' => 'primary',
                        default => 'gray',
                    })
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
                    ->successNotificationTitle('Registo criado com sucesso!')
                    ->label('Novo Utilizador'),
            ])
            ->actions([
                \Filament\Actions\Action::make('verAtividades')
                    ->label('Atividades')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('info')
                    ->modalHeading(fn (User $record) => 'Atividades - ' . $record->name)
                    ->modalWidth('5xl')
                    ->form(function (User $record) {
                        $activities = \App\Models\ActivityLog::where('user_id', $record->id)
                            ->orderBy('created_at', 'desc')
                            ->limit(30)
                            ->get();
                        
                        $loginCount = $activities->where('action', 'login')->count();
                        $createCount = $activities->where('action', 'create')->count();
                        $updateCount = $activities->where('action', 'update')->count();
                        $deleteCount = $activities->where('action', 'delete')->count();
                        
                        $schema = [
                            \Filament\Schemas\Components\Section::make('Resumo do Utilizador')
                                ->schema([
                                    Forms\Components\Placeholder::make('user_info')
                                        ->label('Utilizador')
                                        ->content($record->name . ' (' . $record->email . ')'),
                                    Forms\Components\Placeholder::make('session_status')
                                        ->label('Estado da Sessão')
                                        ->content(new \Illuminate\Support\HtmlString(
                                            $record->current_session_id 
                                                ? '<span class="inline-flex items-center gap-1.5 px-2 py-1 text-xs font-semibold rounded-full bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400"><span class="w-2 h-2 rounded-full bg-success-500 animate-pulse"></span> Online</span>'
                                                : '<span class="inline-flex items-center gap-1.5 px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600 dark:bg-gray-500/20 dark:text-gray-400"><span class="w-2 h-2 rounded-full bg-gray-400"></span> Offline</span>'
                                        )),
                                    Forms\Components\Placeholder::make('last_login')
                                        ->label('Último Login')
                                        ->content($record->last_login_at 
                                            ? \Carbon\Carbon::parse($record->last_login_at)->format('d/m/Y H:i') . ' (IP: ' . ($record->last_login_ip ?? 'N/A') . ')'
                                            : 'Nunca fez login'),
                                ])->columns(3),
                            
                            \Filament\Schemas\Components\Section::make('Estatísticas')
                                ->schema([
                                    Forms\Components\Placeholder::make('stat_login')
                                        ->label('Logins')
                                        ->content(new \Illuminate\Support\HtmlString(
                                            '<span class="inline-flex items-center justify-center px-3 py-1 text-sm font-semibold rounded-full bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400">' . $loginCount . '</span>'
                                        )),
                                    Forms\Components\Placeholder::make('stat_create')
                                        ->label('Criações')
                                        ->content(new \Illuminate\Support\HtmlString(
                                            '<span class="inline-flex items-center justify-center px-3 py-1 text-sm font-semibold rounded-full bg-primary-100 text-primary-700 dark:bg-primary-500/20 dark:text-primary-400">' . $createCount . '</span>'
                                        )),
                                    Forms\Components\Placeholder::make('stat_update')
                                        ->label('Edições')
                                        ->content(new \Illuminate\Support\HtmlString(
                                            '<span class="inline-flex items-center justify-center px-3 py-1 text-sm font-semibold rounded-full bg-warning-100 text-warning-700 dark:bg-warning-500/20 dark:text-warning-400">' . $updateCount . '</span>'
                                        )),
                                    Forms\Components\Placeholder::make('stat_delete')
                                        ->label('Exclusões')
                                        ->content(new \Illuminate\Support\HtmlString(
                                            '<span class="inline-flex items-center justify-center px-3 py-1 text-sm font-semibold rounded-full bg-danger-100 text-danger-700 dark:bg-danger-500/20 dark:text-danger-400">' . $deleteCount . '</span>'
                                        )),
                                ])->columns(4),
                        ];
                        
                        // Adicionar atividades como placeholders
                        if ($activities->isEmpty()) {
                            $schema[] = Forms\Components\Placeholder::make('no_activities')
                                ->label('')
                                ->content('Nenhuma atividade registada para este utilizador.')
                                ->columnSpanFull();
                        } else {
                            $activityItems = [];
                            foreach ($activities as $index => $activity) {
                                // Badges de texto compactos para ações
                                $actionBadge = match($activity->action) {
                                    'login' => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400">Login</span>',
                                    'logout' => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded bg-gray-100 text-gray-700 dark:bg-gray-500/20 dark:text-gray-400">Logout</span>',
                                    'create' => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded bg-primary-100 text-primary-700 dark:bg-primary-500/20 dark:text-primary-400">Criar</span>',
                                    'update' => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded bg-warning-100 text-warning-700 dark:bg-warning-500/20 dark:text-warning-400">Editar</span>',
                                    'delete' => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded bg-danger-100 text-danger-700 dark:bg-danger-500/20 dark:text-danger-400">Excluir</span>',
                                    'view' => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded bg-info-100 text-info-700 dark:bg-info-500/20 dark:text-info-400">Ver</span>',
                                    default => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded bg-gray-100 text-gray-600">' . ucfirst($activity->action) . '</span>',
                                };
                                
                                // Labels de texto para dispositivos
                                $deviceLabel = match($activity->device_type) {
                                    'mobile' => '📱 Mobile',
                                    'tablet' => '📱 Tablet',
                                    default => '💻 Desktop',
                                };
                                
                                $content = new \Illuminate\Support\HtmlString(sprintf(
                                    '<div class="flex items-center gap-2 flex-wrap text-sm">%s <span class="text-gray-500">•</span> <span class="text-gray-600 dark:text-gray-400">%s</span> <span class="text-gray-500">•</span> <span class="text-gray-500">%s</span> <span class="text-gray-500">•</span> <span class="text-gray-500">%s/%s</span> <span class="text-gray-500">•</span> <span class="font-mono text-xs text-gray-400">%s</span></div>',
                                    $actionBadge,
                                    $activity->module ?? '-',
                                    $deviceLabel,
                                    $activity->browser ?? 'N/A',
                                    $activity->platform ?? 'N/A',
                                    $activity->ip_address ?? 'N/A'
                                ));
                                
                                $activityItems[] = Forms\Components\Placeholder::make('activity_' . $index)
                                    ->label($activity->created_at->format('d/m/Y H:i:s'))
                                    ->content($content);
                            }
                            
                            $schema[] = \Filament\Schemas\Components\Section::make('Últimas ' . $activities->count() . ' Atividades')
                                ->schema($activityItems)
                                ->collapsible();
                        }
                        
                        // Link para ver todas
                        $schema[] = Forms\Components\Placeholder::make('ver_todas')
                            ->label('')
                            ->content(new \Illuminate\Support\HtmlString(
                                '<a href="/admin/activity-logs?tableFilters[user_id][value]=' . $record->id . '" 
                                    class="text-primary-600 hover:text-primary-500 font-medium" 
                                    target="_blank">
                                    Ver todas as atividades →
                                </a>'
                            ))
                            ->columnSpanFull();
                        
                        return $schema;
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fechar'),
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
            \Tapp\FilamentAuditing\RelationManagers\AuditsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
        ];
    }
}
