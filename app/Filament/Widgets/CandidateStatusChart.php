<?php

namespace App\Filament\Widgets;

use App\Models\Candidate;
use App\Models\Student;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class CandidateStatusChart extends ChartWidget
{
    protected ?string $heading = 'Estado de Formandos';
    protected static ?int $sort = 3;
    protected ?string $pollingInterval = null;
    protected static bool $isLazy = true;
    
    protected function getData(): array
    {
        // Contar Candidates por tipo de aluno (student_type)
        $candidateCounts = Candidate::query()
            ->selectRaw("student_type, COUNT(*) as total")
            ->whereNotNull('student_type')
            ->where('student_type', '!=', '')
            ->groupBy('student_type')
            ->pluck('total', 'student_type')
            ->toArray();

        // Contar Students por status (Agentes em formação, concluídos, etc.)
        $studentCounts = Student::query()
            ->selectRaw("status, COUNT(*) as total")
            ->whereNotNull('status')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Mapear status dos Students para nomes legíveis
        $statusLabels = [
            'em_formacao' => 'Agentes em Formação',
            'concluiu' => 'Formação Concluída',
            'frequenta' => 'Frequentando',
            'desistiu' => 'Desistência',
            'transferido' => 'Transferido',
        ];

        // Combinar os dados
        $allCounts = [];
        
        // Adicionar Candidates
        foreach ($candidateCounts as $type => $count) {
            $allCounts[$type] = ($allCounts[$type] ?? 0) + $count;
        }
        
        // Adicionar Students com labels legíveis
        foreach ($studentCounts as $status => $count) {
            $label = $statusLabels[$status] ?? ucfirst($status);
            $allCounts[$label] = ($allCounts[$label] ?? 0) + $count;
        }

        // Se não houver dados
        if (empty($allCounts)) {
            $allCounts = ['Sem dados' => 0];
        }

        $colors = [
            'rgba(59, 130, 246, 0.8)',   // Azul
            'rgba(16, 185, 129, 0.8)',   // Verde
            'rgba(245, 158, 11, 0.8)',   // Amarelo
            'rgba(239, 68, 68, 0.8)',    // Vermelho
            'rgba(139, 92, 246, 0.8)',   // Roxo
            'rgba(236, 72, 153, 0.8)',   // Rosa
            'rgba(20, 184, 166, 0.8)',   // Teal
            'rgba(249, 115, 22, 0.8)',   // Laranja
            'rgba(99, 102, 241, 0.8)',   // Indigo
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Formandos',
                    'data' => array_values($allCounts),
                    'backgroundColor' => array_slice($colors, 0, count($allCounts)),
                    'borderWidth' => 0,
                ],
            ],
            'labels' => array_keys($allCounts),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
