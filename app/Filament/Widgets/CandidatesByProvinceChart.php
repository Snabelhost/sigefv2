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
                            'rgba(252, 165, 165, 0.4)', // Rosa suave
                            'rgba(253, 186, 116, 0.4)', // Laranja suave
                            'rgba(253, 224, 71, 0.4)',  // Amarelo suave
                            'rgba(110, 231, 183, 0.4)', // Verde suave
                            'rgba(147, 197, 253, 0.4)', // Azul suave
                            'rgba(167, 139, 250, 0.4)', // Roxo suave
                            'rgba(244, 114, 182, 0.4)', // Rosa choque suave
                            'rgba(45, 212, 191, 0.4)',  // Teall suave
                            'rgba(251, 146, 60, 0.4)',  // Laranja forte suave
                            'rgba(125, 211, 252, 0.4)', // Sky suave
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
