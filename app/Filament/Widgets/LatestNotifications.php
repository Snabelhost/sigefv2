<?php

namespace App\Filament\Widgets;

use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class LatestNotifications extends BaseWidget
{
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Últimas Notificações';

    public function table(Table $table): Table
    {
        $user = Auth::user();

        return $table
            ->query(
                $user->notifications()->getQuery()->latest()->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('data.title')
                    ->label('Título')
                    ->icon(fn ($record) => $record->data['icon'] ?? 'heroicon-s-bell')
                    ->color(fn ($record) => $record->data['color'] ?? 'primary')
                    ->searchable(),
                Tables\Columns\TextColumn::make('data.message')
                    ->label('Mensagem')
                    ->limit(60)
                    ->wrap(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\IconColumn::make('read_at')
                    ->label('Lida')
                    ->boolean()
                    ->trueIcon('heroicon-s-check-circle')
                    ->falseIcon('heroicon-s-clock')
                    ->getStateUsing(fn ($record) => $record->read_at !== null),
            ])
            ->actions([
                Action::make('markAsRead')
                    ->label('Marcar como lida')
                    ->icon('heroicon-s-check')
                    ->action(fn (Model $record) => $record->markAsRead())
                    ->hidden(fn (Model $record) => $record->read_at !== null),
                Action::make('view')
                    ->label('Ver')
                    ->icon('heroicon-s-eye')
                    ->url(fn (Model $record) => $record->data['action_url'] ?? '#')
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading('Sem notificações')
            ->emptyStateDescription('Não existem notificações pendentes.')
            ->emptyStateIcon('heroicon-s-bell-slash');
    }
}

