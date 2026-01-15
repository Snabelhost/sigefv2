<?php

namespace App\Filament\Widgets;

use App\Models\Candidate;
use App\Models\Student;
use App\Models\Trainer;
use App\Models\Institution;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class StatsOverview extends BaseWidget
{
    protected ?string $pollingInterval = null; // Desativa polling automático
    protected static bool $isLazy = true;
    
    protected function getStats(): array
    {
        // Cache por 5 minutos para evitar queries repetidas
        return Cache::remember('dashboard_stats', 300, function () {
            return [
                Stat::make('Total de Alunos', Candidate::count() + Student::count())
                    ->description('Alistados, Recrutas, Instruendos e Agentes')
                    ->descriptionIcon('heroicon-m-identification')
                    ->color('primary'),

                Stat::make('Formandos Activos', Student::whereIn('status', ['em_formacao', 'frequenta'])->count())
                    ->description('Agentes, Recrutas e Instruendos em formação')
                    ->descriptionIcon('heroicon-m-academic-cap')
                    ->color('success'),

                Stat::make('Formadores', Trainer::where('is_active', true)->count())
                    ->description('Corpo docente activo')
                    ->descriptionIcon('heroicon-m-user-group')
                    ->color('info'),

                Stat::make('Escolas de Formação', Institution::count())
                    ->description('Total de instituições de ensino')
                    ->descriptionIcon('heroicon-m-building-office-2')
                    ->color('warning'),
            ];
        });
    }
}
