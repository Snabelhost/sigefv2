<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Provenance;

class ProvenanceSeeder extends Seeder
{
    public function run(): void
    {
        $provenances = [
            // Comando Geral e Direcções Nacionais
            ['name' => 'Comando Geral da Polícia Nacional', 'acronym' => 'CGPN'],
            ['name' => 'Direcção Nacional de Investigação Criminal', 'acronym' => 'DNIC'],
            ['name' => 'Direcção Nacional de Ordem Pública', 'acronym' => 'DNOP'],
            ['name' => 'Direcção Nacional de Trânsito e Segurança Rodoviária', 'acronym' => 'DNTSR'],
            ['name' => 'Direcção Nacional de Protecção e Guarda', 'acronym' => 'DNPG'],
            ['name' => 'Direcção Nacional de Identificação Civil', 'acronym' => 'DNIC-ID'],
            ['name' => 'Direcção Nacional de Recursos Humanos', 'acronym' => 'DNRH'],
            ['name' => 'Direcção Nacional de Logística e Finanças', 'acronym' => 'DNLF'],
            ['name' => 'Direcção Nacional de Informação e Comunicação', 'acronym' => 'DNIC-COM'],
            
            // Unidades Especiais
            ['name' => 'Unidade de Intervenção Rápida', 'acronym' => 'UIR'],
            ['name' => 'Unidade de Protecção de Objectos Estratégicos', 'acronym' => 'UPOE'],
            ['name' => 'Unidade Táctica de Polícia', 'acronym' => 'UTP'],
            ['name' => 'Corpo de Polícia de Segurança Pública', 'acronym' => 'CPSP'],
            ['name' => 'Corpo de Polícia Fiscal e Aduaneira', 'acronym' => 'CPFA'],
            
            // Gabinetes
            ['name' => 'Gabinete de Inspecção', 'acronym' => 'GI'],
            ['name' => 'Gabinete de Estudos e Planeamento', 'acronym' => 'GEP'],
            ['name' => 'Gabinete Jurídico', 'acronym' => 'GJ'],
            
            // Instituições de Formação
            ['name' => 'Escola Prática de Polícia', 'acronym' => 'EPP'],
            ['name' => 'Instituto Superior de Ciências Policiais e Criminais', 'acronym' => 'ISCPC'],
            ['name' => 'Centro de Instrução Policial', 'acronym' => 'CIP'],
            
            // Comandos Provinciais
            ['name' => 'Comando Provincial de Luanda', 'acronym' => 'CP-Luanda'],
            ['name' => 'Comando Provincial de Benguela', 'acronym' => 'CP-Benguela'],
            ['name' => 'Comando Provincial do Huambo', 'acronym' => 'CP-Huambo'],
            ['name' => 'Comando Provincial de Cabinda', 'acronym' => 'CP-Cabinda'],
            ['name' => 'Comando Provincial do Namibe', 'acronym' => 'CP-Namibe'],
            ['name' => 'Comando Provincial da Huíla', 'acronym' => 'CP-Huíla'],
            ['name' => 'Comando Provincial do Cuanza Sul', 'acronym' => 'CP-Cuanza Sul'],
            ['name' => 'Comando Provincial do Cuanza Norte', 'acronym' => 'CP-Cuanza Norte'],
            ['name' => 'Comando Provincial do Uíge', 'acronym' => 'CP-Uíge'],
            ['name' => 'Comando Provincial do Zaire', 'acronym' => 'CP-Zaire'],
            ['name' => 'Comando Provincial de Malanje', 'acronym' => 'CP-Malanje'],
            ['name' => 'Comando Provincial da Lunda Norte', 'acronym' => 'CP-Lunda Norte'],
            ['name' => 'Comando Provincial da Lunda Sul', 'acronym' => 'CP-Lunda Sul'],
            ['name' => 'Comando Provincial do Moxico', 'acronym' => 'CP-Moxico'],
            ['name' => 'Comando Provincial do Cuando Cubango', 'acronym' => 'CP-Cuando Cubango'],
            ['name' => 'Comando Provincial do Cunene', 'acronym' => 'CP-Cunene'],
            ['name' => 'Comando Provincial do Bié', 'acronym' => 'CP-Bié'],
            ['name' => 'Comando Provincial do Bengo', 'acronym' => 'CP-Bengo'],
        ];

        foreach ($provenances as $provenance) {
            Provenance::updateOrCreate(
                ['acronym' => $provenance['acronym']],
                $provenance
            );
        }
    }
}
