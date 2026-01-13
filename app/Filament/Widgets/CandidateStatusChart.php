<?php

namespace App\Filament\Widgets;

use App\Models\Candidate;
use Filament\Widgets\ChartWidget;

class CandidateStatusChart extends ChartWidget
{
    protected ?string $heading = 'Estado dos Candidatos';
    protected static ?int $sort = 3;
    protected ?string $pollingInterval = null; // Desativa polling automático
    protected static bool $isLazy = true;
    
    protected function getData(): array
    {
        // Uma única query em vez de 4 queries separadas
        $counts = Candidate::query()
            ->selectRaw("status, COUNT(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Estado',
                    'data' => [
                        $counts['pending'] ?? 0,
                        $counts['approved'] ?? 0,
                        $counts['rejected'] ?? 0,
                        $counts['admitted'] ?? 0,
                    ],
                    'backgroundColor' => [
                        'rgba(253, 186, 116, 0.4)', // Pendentes
                        'rgba(110, 231, 183, 0.4)', // Aprovados
                        'rgba(252, 165, 165, 0.4)', // Rejeitados
                        'rgba(147, 197, 253, 0.4)', // Admitidos
                    ],
                    'borderWidth' => 0,
                ],
            ],
            'labels' => ['Pendentes', 'Aprovados', 'Rejeitados', 'Admitidos'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
