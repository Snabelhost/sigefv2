<?php

namespace App\Filament\Escola\Widgets;

use App\Models\Student;
use App\Models\Evaluation;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EscolaStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $tenant = Filament::getTenant();
        $institutionId = $tenant?->id;

        return [
            Stat::make('Formandos Activos', Student::where('institution_id', $institutionId)
                ->where('status', 'frequenta')->count())
                ->description('Em formação')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),

            Stat::make('Total Avaliações', Evaluation::where('institution_id', $institutionId)->count())
                ->description('Registadas este ano')
                ->descriptionIcon('heroicon-m-document-check')
                ->color('info'),

            Stat::make('Média Geral', number_format(
                Evaluation::where('institution_id', $institutionId)->avg('score') ?? 0, 1
            ))
                ->description('Notas de 0 a 20')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color(Evaluation::where('institution_id', $institutionId)->avg('score') >= 10 ? 'success' : 'danger'),

            Stat::make('Dispensas Activas', \App\Models\StudentLeave::where('institution_id', $institutionId)
                ->where('status', 'approved')
                ->where('end_date', '>=', now())
                ->count())
                ->description('Formandos ausentes')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning'),
        ];
    }
}
