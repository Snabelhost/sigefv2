<?php

namespace App\Filament\Widgets;

use App\Models\Candidate;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class CandidatesByProvinceChart extends ChartWidget
{
    protected ?string $heading = 'Candidatos por Província';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected ?string $pollingInterval = null; // Desativa polling automático
    protected static bool $isLazy = true;

    protected function getData(): array
    {
        // Forçamos a limpeza do cache para aplicar as novas cores transparentes
        Cache::forget('candidates_by_province_chart');

        return Cache::remember('candidates_by_province_chart', 300, function () {
            $data = Candidate::join('provenances', 'candidates.provenance_id', '=', 'provenances.id')
                ->select('provenances.name', DB::raw('count(*) as total'))
                ->groupBy('provenances.name')
                ->orderByDesc('total')
                ->limit(10)
                ->get();

            return [
                'datasets' => [
                    [
                        'label' => 'Candidatos',
                        'data' => $data->pluck('total')->toArray(),
                        'backgroundColor' => [
                            'rgba(252, 165, 165, 1)', // Rosa
                            'rgba(253, 186, 116, 1)', // Laranja
                            'rgba(253, 224, 71, 1)',  // Amarelo
                            'rgba(110, 231, 183, 1)', // Verde
                            'rgba(147, 197, 253, 1)', // Azul
                            'rgba(167, 139, 250, 1)', // Roxo
                            'rgba(244, 114, 182, 1)', // Rosa choque
                            'rgba(45, 212, 191, 1)',  // Teal
                            'rgba(251, 146, 60, 1)',  // Laranja forte
                            'rgba(125, 211, 252, 1)', // Sky
                        ],
                        'borderWidth' => 0, // Sem bordas conforme solicitado
                    ],
                ],
                'labels' => $data->pluck('name')->toArray(),
            ];
        });
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
