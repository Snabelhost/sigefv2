<?php

namespace App\Filament\Widgets;

use App\Models\Candidate;
use App\Models\Student;
use App\Models\Institution;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class CandidatesByProvinceChart extends ChartWidget
{
    protected ?string $heading = 'Alunos por Instituição de Ensino';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected ?string $pollingInterval = null; // Desativa polling automático
    protected static bool $isLazy = true;

    protected function getData(): array
    {
        // Limpar cache para refletir alterações
        Cache::forget('students_by_institution_chart');

        return Cache::remember('students_by_institution_chart', 300, function () {
            // Obter todas as instituições
            $institutions = Institution::all();
            
            $labels = [];
            $totals = [];
            
            foreach ($institutions as $institution) {
                // Contar Candidates desta instituição
                $candidateCount = Candidate::where('institution_id', $institution->id)->count();
                
                // Contar Students desta instituição
                $studentCount = Student::where('institution_id', $institution->id)->count();
                
                // Total de alunos
                $total = $candidateCount + $studentCount;
                
                if ($total > 0) {
                    $labels[] = $institution->name;
                    $totals[] = $total;
                }
            }
            
            // Ordenar por total decrescente e limitar a 10
            $combined = array_combine($labels, $totals);
            arsort($combined);
            $combined = array_slice($combined, 0, 10, true);
            
            return [
                'datasets' => [
                    [
                        'label' => 'Alunos',
                        'data' => array_values($combined),
                        'backgroundColor' => [
                            'rgba(59, 130, 246, 0.8)',   // Azul
                            'rgba(16, 185, 129, 0.8)',  // Verde
                            'rgba(245, 158, 11, 0.8)',  // Amarelo
                            'rgba(239, 68, 68, 0.8)',   // Vermelho
                            'rgba(139, 92, 246, 0.8)',  // Roxo
                            'rgba(236, 72, 153, 0.8)',  // Rosa
                            'rgba(20, 184, 166, 0.8)',  // Teal
                            'rgba(249, 115, 22, 0.8)',  // Laranja
                            'rgba(99, 102, 241, 0.8)',  // Indigo
                            'rgba(34, 197, 94, 0.8)',   // Verde claro
                        ],
                        'borderWidth' => 0,
                    ],
                ],
                'labels' => array_keys($combined),
            ];
        });
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
