<?php

namespace App\Filament\Widgets;

use App\Models\Student;
use Filament\Widgets\ChartWidget;

class StudentStatusChart extends ChartWidget
{
    protected ?string $heading = 'Estado dos Formandos';
    protected static ?int $sort = 4;

    protected ?string $pollingInterval = null; // Desativa polling automático
    protected static bool $isLazy = true;
    
    protected function getData(): array
    {
        $statusLabels = [
            'alistado' => 'Alistados',
            'frequenta' => 'Em Formação',
            'concluiu' => 'Concluídos',
            'desistiu' => 'Desistências',
            'expulso' => 'Expulsos',
        ];
        
        $colors = [
            'alistado' => 'rgba(253, 186, 116, 0.4)',  // Laranja
            'frequenta' => 'rgba(147, 197, 253, 0.4)', // Azul
            'concluiu' => 'rgba(110, 231, 183, 0.4)',  // Verde
            'desistiu' => 'rgba(252, 165, 165, 0.4)',  // Vermelho/Rosa suave
            'expulso' => 'rgba(244, 114, 182, 0.4)',   // Rosa choque suave
        ];

        // Uma única query em vez de 5 queries separadas
        $counts = Student::query()
            ->selectRaw("status, COUNT(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $data = [];
        $labels = [];
        $bgColors = [];

        foreach ($statusLabels as $key => $label) {
            if (isset($counts[$key]) && $counts[$key] > 0) {
                $data[] = $counts[$key];
                $labels[] = $label;
                $bgColors[] = $colors[$key];
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Formandos',
                    'data' => $data,
                    'backgroundColor' => $bgColors,
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
