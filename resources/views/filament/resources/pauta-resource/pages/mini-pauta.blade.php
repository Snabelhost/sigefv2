<x-filament-panels::page>
    <div class="space-y-8">
        {{-- Cabeçalho --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10" style="padding: 30px 35px;">
            <table class="w-full">
                <tr>
                    <td class="align-top" style="width: 140px; padding-right: 30px;">
                        <img src="{{ asset('images/logo-pna.png') }}" alt="Logo PNA" style="width: 120px; height: auto;">
                    </td>
                    <td class="align-top">
                        <h2 style="font-size: 18px; font-weight: bold; color: #041B4E; margin-bottom: 20px;">
                            MINI PAUTA DO PROFESSOR : {{ $this->getTrainerName() }}
                        </h2>
                        
                        <table style="font-size: 14px;">
                            {{-- Curso --}}
                            <tr>
                                <td style="font-weight: bold; color: #1f2937; padding: 8px 15px 8px 0; width: 100px;"><strong>Curso:</strong></td>
                                <td style="padding: 8px 0;">
                                    <select wire:model.live="course_id" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white" style="min-width: 400px; padding: 8px 12px; font-size: 14px;">
                                        <option value="">Seleccione o curso...</option>
                                        @foreach(\App\Models\Course::all() as $course)
                                            <option value="{{ $course->id }}">{{ $course->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                            {{-- Turma --}}
                            <tr>
                                <td style="font-weight: bold; color: #1f2937; padding: 8px 15px 8px 0;"><strong>Turma:</strong></td>
                                <td style="padding: 8px 0;">
                                    <select wire:model.live="class_id" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white" style="min-width: 400px; padding: 8px 12px; font-size: 14px;" @if(!$this->course_id) disabled @endif>
                                        <option value="">Seleccione a turma...</option>
                                        @foreach($this->getClasses() as $class)
                                            <option value="{{ $class->id }}">{{ $class->name }} - {{ $class->institution->name ?? '' }}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                            {{-- Disciplina --}}
                            <tr>
                                <td style="font-weight: bold; color: #1f2937; padding: 8px 15px 8px 0;"><strong>Disciplina:</strong></td>
                                <td style="padding: 8px 0;">
                                    <select wire:model.live="subject_id" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white" style="min-width: 400px; padding: 8px 12px; font-size: 14px;" @if(!$this->class_id) disabled @endif>
                                        <option value="">Seleccione a disciplina...</option>
                                        @foreach(\App\Models\Subject::all() as $subject)
                                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                            {{-- Informações extras --}}
                            @if($this->class_id && $this->getSelectedClass())
                                @php $selectedClass = $this->getSelectedClass(); @endphp
                                <tr>
                                    <td style="font-weight: bold; color: #1f2937; padding: 8px 15px 8px 0;"><strong>Instituição:</strong></td>
                                    <td style="padding: 8px 0; color: #374151;">{{ $selectedClass->institution->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold; color: #1f2937; padding: 8px 15px 8px 0;"><strong>Ano:</strong></td>
                                    <td style="padding: 8px 0; color: #374151;">{{ $selectedClass->academicYear->year ?? '-' }}</td>
                                </tr>
                            @endif
                        </table>
                        
                        {{-- Botão Pesquisar --}}
                        <div style="margin-top: 20px;">
                            <x-filament::button 
                                wire:click="pesquisar"
                                icon="heroicon-o-magnifying-glass"
                                :disabled="!$this->class_id || !$this->subject_id"
                            >
                                Pesquisar
                            </x-filament::button>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        {{-- Tabela de Notas --}}
        @if($this->showTable)
            <div style="margin-top: 25px;">
                {{ $this->table }}
            </div>
        @endif
    </div>
</x-filament-panels::page>
