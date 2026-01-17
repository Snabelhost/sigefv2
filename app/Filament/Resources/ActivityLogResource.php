<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Models\ActivityLog;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-s-clipboard-document-list';
    protected static string|\UnitEnum|null $navigationGroup = 'Gestão de Acesso';
    protected static ?int $navigationSort = 3;
    protected static ?string $modelLabel = 'Atividade';
    protected static ?string $pluralModelLabel = 'Log de Atividades';
    protected static ?string $navigationLabel = 'Log de Atividades';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user')->latest();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Schema $form): Schema
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->striped()
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data/Hora')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Utilizador')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user'),
                Tables\Columns\TextColumn::make('action')
                    ->label('Ação')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'login' => 'Login',
                        'logout' => 'Logout',
                        'create' => 'Criar',
                        'update' => 'Editar',
                        'delete' => 'Excluir',
                        'view' => 'Ver',
                        default => ucfirst($state),
                    })
                    ->color(fn ($state) => match($state) {
                        'login' => 'success',
                        'logout' => 'gray',
                        'create' => 'primary',
                        'update' => 'warning',
                        'delete' => 'danger',
                        'view' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('module')
                    ->label('Módulo')
                    ->searchable()
                    ->icon('heroicon-o-rectangle-stack'),
                Tables\Columns\TextColumn::make('description')
                    ->label('Descrição')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->description),
                Tables\Columns\TextColumn::make('device_type')
                    ->label('Dispositivo')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'mobile' => 'Mobile',
                        'tablet' => 'Tablet',
                        'desktop' => 'Desktop',
                        default => $state ?? 'N/A',
                    })
                    ->icon(fn ($state) => match($state) {
                        'mobile' => 'heroicon-o-device-phone-mobile',
                        'tablet' => 'heroicon-o-device-tablet',
                        default => 'heroicon-o-computer-desktop',
                    })
                    ->color('gray'),
                Tables\Columns\TextColumn::make('browser')
                    ->label('Browser')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('platform')
                    ->label('Sistema')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->fontFamily('mono')
                    ->copyable()
                    ->copyMessage('IP copiado!')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->label('Tipo de Ação')
                    ->options([
                        'login' => 'Login',
                        'logout' => 'Logout',
                        'create' => 'Criar',
                        'update' => 'Editar',
                        'delete' => 'Excluir',
                        'view' => 'Ver',
                    ]),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Utilizador')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('device_type')
                    ->label('Dispositivo')
                    ->options([
                        'desktop' => 'Desktop',
                        'mobile' => 'Mobile',
                        'tablet' => 'Tablet',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('De'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([])
            ->actions([
                \Filament\Actions\Action::make('verDetalhes')
                    ->label('Detalhes')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading('Detalhes da Atividade')
                    ->modalWidth('lg')
                    ->form([
                        \Filament\Schemas\Components\Section::make('Informações Gerais')
                            ->schema([
                                Forms\Components\Placeholder::make('user_name')
                                    ->label('Utilizador')
                                    ->content(fn ($record) => $record->user?->name ?? 'N/A'),
                                Forms\Components\Placeholder::make('session_status')
                                    ->label('Estado da Sessão')
                                    ->content(fn ($record) => new \Illuminate\Support\HtmlString(
                                        $record->user?->current_session_id 
                                            ? '<span class="inline-flex items-center gap-1.5 px-2 py-0.5 text-xs font-semibold rounded-full bg-success-100 text-success-700"><span class="w-1.5 h-1.5 rounded-full bg-success-500 animate-pulse"></span> Online</span>'
                                            : '<span class="inline-flex items-center gap-1.5 px-2 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-600"><span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Offline</span>'
                                    )),
                                Forms\Components\Placeholder::make('action_display')
                                    ->label('Ação')
                                    ->content(fn ($record) => new \Illuminate\Support\HtmlString(match($record->action) {
                                        'login' => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded bg-success-100 text-success-700">Login</span>',
                                        'logout' => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded bg-gray-100 text-gray-700">Logout</span>',
                                        'create' => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded bg-primary-100 text-primary-700">Criar</span>',
                                        'update' => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded bg-warning-100 text-warning-700">Editar</span>',
                                        'delete' => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded bg-danger-100 text-danger-700">Excluir</span>',
                                        'view' => '<span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded bg-info-100 text-info-700">Ver</span>',
                                        default => '<span class="text-gray-600">' . ucfirst($record->action) . '</span>',
                                    })),
                                Forms\Components\Placeholder::make('module_display')
                                    ->label('Módulo')
                                    ->content(fn ($record) => $record->module ?? '-'),
                                Forms\Components\Placeholder::make('description_display')
                                    ->label('Descrição')
                                    ->content(fn ($record) => $record->description ?? '-'),
                                Forms\Components\Placeholder::make('created_at_display')
                                    ->label('Data/Hora')
                                    ->content(fn ($record) => $record->created_at->format('d/m/Y H:i:s')),
                            ])->columns(3),
                        \Filament\Schemas\Components\Section::make('Dispositivo e Rede')
                            ->schema([
                                Forms\Components\Placeholder::make('device_display')
                                    ->label('Tipo de Dispositivo')
                                    ->content(fn ($record) => match($record->device_type) {
                                        'mobile' => '📱 Mobile',
                                        'tablet' => '📱 Tablet',
                                        default => '💻 Desktop',
                                    }),
                                Forms\Components\Placeholder::make('browser_display')
                                    ->label('Browser')
                                    ->content(fn ($record) => $record->browser ?? 'N/A'),
                                Forms\Components\Placeholder::make('platform_display')
                                    ->label('Sistema Operativo')
                                    ->content(fn ($record) => $record->platform ?? 'N/A'),
                                Forms\Components\Placeholder::make('ip_display')
                                    ->label('Endereço IP')
                                    ->content(fn ($record) => $record->ip_address ?? 'N/A'),
                            ])->columns(2),
                        \Filament\Schemas\Components\Section::make('URL Acessada')
                            ->schema([
                                Forms\Components\Placeholder::make('url_display')
                                    ->label('')
                                    ->content(fn ($record) => $record->url ?? 'N/A'),
                            ])->collapsed(),
                    ])
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fechar'),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
        ];
    }
}
