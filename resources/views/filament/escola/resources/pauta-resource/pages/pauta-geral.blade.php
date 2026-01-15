<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Info da Turma --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-4">
            <div class="flex items-center gap-4">
                <div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Turma:</span>
                    <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ $record->name }}</span>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Ano Académico:</span>
                    <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ $record->academicYear?->year }}</span>
                </div>
                <div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Disciplinas:</span>
                    <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ count($subjects) }}</span>
                </div>
            </div>
        </div>

        {{-- Tabela de Notas Geral --}}
        {{ $this->table }}
    </div>
</x-filament-panels::page>
