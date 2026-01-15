<?php

namespace App\Filament\Widgets;

use App\Models\Student;
use Filament\Widgets\ChartWidget;

class StudentStatusChart extends ChartWidget
{
    protected ?string $heading = 'Formandos Aprovados e Reprovados';
    protected static ?int $sort = 4;
    protected ?string $pollingInterval = null;
    protected static bool $isLazy = true;
    
    protected function getData(): array
    {
        // Contar aprovados/concluídos (inclui status positivos)
        $aprovados = Student::whereIn('status', ['concluiu', 'formado', 'aprovado', 'frequenta', 'em_formacao'])->count();
        
        // Contar reprovados/desistências (inclui status negativos)
        $reprovados = Student::whereIn('status', ['reprovado', 'desistiu', 'expulso', 'transferido'])->count();

        // Se não houver reprovados, definir um mínimo para visualização
        if ($aprovados == 0 && $reprovados == 0) {
            $aprovados = 1; // Valor mínimo para mostrar algo
        }

        return [
            'datasets' => [
                [
                    'label' => 'Formandos',
                    'data' => [$aprovados, $reprovados],
                    'backgroundColor' => [
                        'rgba(16, 185, 129, 0.9)',   // Verde - Aprovados/Em Formação
                        'rgba(239, 68, 68, 0.9)',    // Vermelho - Reprovados/Desistências
                    ],
                    'borderWidth' => 0,
                ],
            ],
            'labels' => ['Aprovados/Em Formação', 'Reprovados/Desistências'],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
