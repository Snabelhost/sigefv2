<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Cabeçalho --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6">
            <table class="w-full">
                <tr>
                    <td class="w-32 align-top pr-6">
                        <img src="{{ asset('images/logo-pna.png') }}" alt="Logo PNA" style="width: 110px; height: auto;">
                    </td>
                    <td class="align-top">
                        <h2 class="text-lg font-bold text-primary-600 dark:text-primary-400 mb-3">
                            PAUTA GERAL DE CLASSIFICAÇÃO
                        </h2>
                        
                        <table class="text-sm">
                            <tr>
                                <td class="font-bold text-gray-700 dark:text-gray-300 py-1 pr-3">Turma:</td>
                                <td class="text-gray-900 dark:text-white py-1">{{ $record->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="font-bold text-gray-700 dark:text-gray-300 py-1 pr-3">Instituição:</td>
                                <td class="text-gray-900 dark:text-white py-1">{{ $record->institution->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="font-bold text-gray-700 dark:text-gray-300 py-1 pr-3">Ano:</td>
                                <td class="text-gray-900 dark:text-white py-1">{{ $record->academicYear->year ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="font-bold text-gray-700 dark:text-gray-300 py-1 pr-3">Curso:</td>
                                <td class="text-gray-900 dark:text-white py-1">{{ $record->courseMap->course->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="font-bold text-gray-700 dark:text-gray-300 py-1 pr-3">Disciplinas:</td>
                                <td class="text-primary-600 dark:text-primary-400 font-semibold py-1">{{ count($subjects ?? []) }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        {{-- Tabela --}}
        {{ $this->table }}
    </div>
</x-filament-panels::page>
