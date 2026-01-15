<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filtro de Disciplina --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-4">
            <div class="flex items-center gap-4">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Disciplina:</label>
                <select wire:model.live="subject_id" class="fi-input rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    @foreach(\App\Models\Subject::where('institution_id', \Filament\Facades\Filament::getTenant()?->id)->get() as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Tabela de Notas --}}
        {{ $this->table }}
    </div>
</x-filament-panels::page>
