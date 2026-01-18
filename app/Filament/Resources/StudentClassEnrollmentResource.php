<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentClassEnrollmentResource\Pages;
use App\Models\StudentClassEnrollment;
use App\Models\Student;
use App\Models\StudentClass;
use App\Models\Subject;
use App\Models\CoursePhase;
use App\Models\AcademicYear;
use App\Models\StudentSubjectEnrollment;
use App\Models\StudentType;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudentClassEnrollmentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-s-user-group';
    protected static string|\UnitEnum|null $navigationGroup = 'Gestão Escolar';
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationLabel = 'Gestão de Formandos';
    protected static ?string $modelLabel = 'Formando';
    protected static ?string $pluralModelLabel = 'Gestão de Formandos';
    protected static ?string $slug = 'student-class-enrollments';

    /**
     * Obter opções de tipos de alunos da base de dados
     */
    public static function getStudentTypeOptions(): array
    {
        return StudentType::where('is_active', true)
            ->orderBy('order')
            ->pluck('name', 'name')
            ->toArray();
    }

    /**
     * Obter cores dos tipos de alunos
     */
    public static function getStudentTypeColors(): array
    {
        return StudentType::where('is_active', true)
            ->pluck('color', 'name')
            ->toArray();
    }

    public static function getEloquentQuery(): Builder
    {
        return Student::query()
            ->whereHas('classEnrollments')
            ->with(['candidate', 'institution', 'currentPhase', 'classEnrollments.studentClass.courseMap.course', 'classEnrollments.coursePhase'])
            ->withCount(['classEnrollments', 'subjectEnrollments'])
            ->withMax('classEnrollments', 'enrolled_at')
            ->orderByDesc('class_enrollments_max_enrolled_at')
            ->orderByDesc('created_at');
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Section::make('Inscrever Aluno em Turma')
                    ->description('Seleccione o aluno, a turma, fase e disciplinas.')
                    ->schema([
                        Forms\Components\Select::make('student_id')
                            ->label('Aluno')
                            ->relationship('student', 'student_number')
                            ->getOptionLabelFromRecordUsing(fn ($record) => ($record->candidate->full_name ?? 'N/A') . ' - ' . $record->student_number)
                            ->required()
                            ->searchable(['student_number']),
                        Forms\Components\Select::make('student_type')
                            ->label('Estado do Aluno')
                            ->options(fn () => self::getStudentTypeOptions())
                            ->required(),
                        Forms\Components\Select::make('class_id')
                            ->label('Turma')
                            ->relationship('studentClass', 'name')
                            ->required()
                            ->searchable(),
                        Forms\Components\Select::make('course_phase_id')
                            ->label('Fase do Curso')
                            ->relationship('coursePhase', 'name')
                            ->searchable(),
                        Forms\Components\TextInput::make('classroom')
                            ->label('Sala de Aula')
                            ->maxLength(50),
                        Forms\Components\Select::make('academic_year_id')
                            ->label('Ano Académico')
                            ->options(fn () => AcademicYear::where('is_active', true)->pluck('name', 'id'))
                            ->searchable(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        $typeColors = self::getStudentTypeColors();
        
        return $table
            ->deferLoading()
            ->striped()
            ->defaultSort('class_enrollments_max_enrolled_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('student_number')
                    ->label('Nº Aluno')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('candidate.full_name')
                    ->label('Nome do Aluno')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('student_type')
                    ->label('Estado')
                    ->badge()
                    ->color(fn ($state) => $typeColors[$state] ?? 'gray')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('cia')
                    ->label('CIA')
                    ->formatStateUsing(fn ($state) => $state ? "{$state}ª CIA" : null)
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('platoon')
                    ->label('Pelotão')
                    ->formatStateUsing(fn ($state) => $state ? "{$state}º Pelotão" : null)
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('section')
                    ->label('Secção')
                    ->formatStateUsing(fn ($state) => $state ? "{$state}ª Secção" : null)
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('classEnrollments')
                    ->label('Curso')
                    ->getStateUsing(fn (Student $record) => 
                        $record->classEnrollments
                            ->map(fn ($e) => $e->studentClass?->courseMap?->course?->name)
                            ->filter()
                            ->unique()
                            ->implode(', ') ?: '-'
                    )
                    ->wrap()
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('institution.name')
                    ->label('Instituição')
                    ->wrap()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('subject_enrollments_count')
                    ->label('Total Disc')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('student_type')
                    ->label('Estado do Aluno')
                    ->options(fn () => self::getStudentTypeOptions()),
                Tables\Filters\SelectFilter::make('institution_id')
                    ->label('Instituição')
                    ->relationship('institution', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('cia')
                    ->label('CIA')
                    ->options(fn () => Student::whereNotNull('cia')->distinct()->pluck('cia', 'cia'))
                    ->searchable(),
                Tables\Filters\SelectFilter::make('platoon')
                    ->label('Pelotão')
                    ->options(fn () => Student::whereNotNull('platoon')->distinct()->pluck('platoon', 'platoon'))
                    ->searchable(),
                Tables\Filters\SelectFilter::make('section')
                    ->label('Secção')
                    ->options(fn () => Student::whereNotNull('section')->distinct()->pluck('section', 'section'))
                    ->searchable(),
            ])
            ->headerActions([
                \Filament\Actions\Action::make('novaInscricao')
                    ->icon('heroicon-o-plus')
                    ->label(function () {
                        // Contar alunos não inscritos
                        $enrolledStudentIds = StudentClassEnrollment::distinct()->pluck('student_id');
                        $notEnrolledCount = Student::whereNotIn('id', $enrolledStudentIds)->count();
                        return "Nova Inscrição ($notEnrolledCount pendentes)";
                    })
                    ->modalHeading('Inscrever Aluno em Turma')
                    ->modalWidth('3xl')
                    ->form([
                        // Aluno - linha inteira (apenas não inscritos)
                        Forms\Components\Select::make('student_id')
                            ->label('Aluno (apenas não inscritos)')
                            ->options(function () {
                                // Buscar IDs dos alunos já inscritos
                                $enrolledStudentIds = StudentClassEnrollment::distinct()->pluck('student_id');
                                
                                return Student::with('candidate')
                                    ->whereNotIn('id', $enrolledStudentIds)
                                    ->get()
                                    ->mapWithKeys(fn ($s) => [
                                        $s->id => ($s->candidate->full_name ?? 'N/A') . ' - ' . $s->student_number . ' (' . ($s->candidate->student_type ?? 'N/A') . ')'
                                    ]);
                            })
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    $student = Student::with('candidate')->find($state);
                                    $candidateType = $student?->candidate?->student_type;
                                    $studentType = $student?->student_type;
                                    
                                    // Verificar se já tem um tipo definido (Formando - Superior)
                                    if ($studentType && str_contains(strtolower($studentType), 'formando') && str_contains(strtolower($studentType), 'superior')) {
                                        $formandoType = StudentType::where('name', 'like', '%Formando%')
                                            ->where('name', 'like', '%superior%')
                                            ->first();
                                        if ($formandoType) {
                                            $set('student_type', $formandoType->name);
                                        }
                                    }
                                    // Auto-preencher Estado baseado no tipo do candidato
                                    elseif ($candidateType === 'Agente') {
                                        // Buscar estado "Formando - superior" ou similar
                                        $formandoType = StudentType::where('name', 'like', '%Formando%')
                                            ->where('name', 'like', '%superior%')
                                            ->first();
                                        if ($formandoType) {
                                            $set('student_type', $formandoType->name);
                                        }
                                    } elseif ($candidateType === 'Alistado') {
                                        // Buscar estado "Recruta"
                                        $recrutaType = StudentType::where('name', 'like', '%Recruta%')->first();
                                        if ($recrutaType) {
                                            $set('student_type', $recrutaType->name);
                                        }
                                    }
                                    // Verificar também pelo student_type do próprio student
                                    elseif ($studentType && str_contains(strtolower($studentType), 'recruta')) {
                                        $recrutaType = StudentType::where('name', 'like', '%Recruta%')->first();
                                        if ($recrutaType) {
                                            $set('student_type', $recrutaType->name);
                                        }
                                    }
                                }
                            })
                            ->helperText('Mostra apenas alunos que ainda não foram inscritos em nenhuma turma')
                            ->columnSpanFull(),
                        
                        // Estado e Curso
                        \Filament\Schemas\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('student_type')
                                    ->label('Estado do Aluno')
                                    ->options(function ($get) {
                                        $studentId = $get('student_id');
                                        if (!$studentId) {
                                            return StudentType::where('is_active', true)->orderBy('order')->pluck('name', 'name')->toArray();
                                        }
                                        
                                        $student = Student::with('candidate')->find($studentId);
                                        $candidateType = $student?->candidate?->student_type;
                                        
                                        // Filtrar opções baseado no tipo do candidato
                                        if ($candidateType === 'Agente') {
                                            // Agentes só podem ter estados de Formando/Superior
                                            return StudentType::where('is_active', true)
                                                ->where(function ($q) {
                                                    $q->where('name', 'like', '%Formando%')
                                                      ->orWhere('name', 'like', '%Instruendo%');
                                                })
                                                ->orderBy('order')
                                                ->pluck('name', 'name')
                                                ->toArray();
                                        } elseif ($candidateType === 'Alistado') {
                                            // Alistados só podem ter estados de Recruta/Instruendo básico
                                            return StudentType::where('is_active', true)
                                                ->where(function ($q) {
                                                    $q->where('name', 'like', '%Recruta%')
                                                      ->orWhere('name', 'like', '%Instruendo%');
                                                })
                                                ->whereNot('name', 'like', '%superior%')
                                                ->orderBy('order')
                                                ->pluck('name', 'name')
                                                ->toArray();
                                        }
                                        
                                        return StudentType::where('is_active', true)->orderBy('order')->pluck('name', 'name')->toArray();
                                    })
                                    ->required()
                                    ->live()
                                    ->helperText(function ($get) {
                                        $studentId = $get('student_id');
                                        if ($studentId) {
                                            $student = Student::with('candidate')->find($studentId);
                                            $candidateType = $student?->candidate?->student_type ?? 'N/A';
                                            return "Tipo: $candidateType";
                                        }
                                        return 'Seleccione um aluno primeiro';
                                    })
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        if ($state) {
                                            // Buscar fase do curso selecionado
                                            $courseId = $get('course_id');
                                            if ($courseId) {
                                                if (str_contains(strtolower($state), 'recruta')) {
                                                    $phase = CoursePhase::where('course_id', $courseId)->where('name', 'like', '%1%')->first();
                                                    if ($phase) $set('course_phase_id', $phase->id);
                                                } elseif (str_contains(strtolower($state), 'instruendo')) {
                                                    $phase = CoursePhase::where('course_id', $courseId)->where('name', 'like', '%2%')->first();
                                                    if ($phase) $set('course_phase_id', $phase->id);
                                                }
                                            }
                                        }
                                    }),
                                Forms\Components\Select::make('course_id')
                                    ->label('Curso')
                                    ->options(fn () => \App\Models\Course::pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($set) {
                                        $set('class_id', null);
                                        $set('course_phase_id', null);
                                        $set('subject_ids', []);
                                    }),
                            ]),
                        
                        // Turma e Fase (filtradas pelo curso)
                        \Filament\Schemas\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('class_id')
                                    ->label('Turma')
                                    ->options(function ($get) {
                                        $courseId = $get('course_id');
                                        if (!$courseId) {
                                            return [];
                                        }
                                        // Filtrar turmas pelo curso através do courseMap
                                        return StudentClass::whereHas('courseMap', fn ($q) => $q->where('course_id', $courseId))
                                            ->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->required()
                                    ->helperText('Seleccione o curso primeiro'),
                                Forms\Components\Select::make('course_phase_id')
                                    ->label('Fase do Curso')
                                    ->options(function ($get) {
                                        $courseId = $get('course_id');
                                        if (!$courseId) {
                                            return [];
                                        }
                                        return CoursePhase::where('course_id', $courseId)->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn ($set) => $set('subject_ids', []))
                                    ->helperText('Seleccione o curso primeiro'),
                            ]),
                        
                        // Sala e Ano
                        \Filament\Schemas\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('classroom')
                                    ->label('Sala de Aula')
                                    ->maxLength(50),
                                Forms\Components\Select::make('academic_year_id')
                                    ->label('Ano Académico')
                                    ->options(fn () => AcademicYear::where('is_active', true)->pluck('name', 'id'))
                                    ->searchable()
                                    ->required(),
                            ]),
                        
                        // CIA, Pelotão e Secção
                        \Filament\Schemas\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('cia')
                                    ->label('CIA')
                                    ->options(collect(range(1, 15))->mapWithKeys(fn ($n) => [$n => "{$n}ª CIA"]))
                                    ->searchable(),
                                Forms\Components\Select::make('platoon')
                                    ->label('Pelotão')
                                    ->options(collect(range(1, 15))->mapWithKeys(fn ($n) => [$n => "{$n}º PELOTÃO"]))
                                    ->searchable(),
                                Forms\Components\Select::make('section')
                                    ->label('Secção')
                                    ->options(collect(range(1, 15))->mapWithKeys(fn ($n) => [$n => "{$n}ª SECÇÃO"]))
                                    ->searchable(),
                            ]),
                        
                        // Disciplinas - última linha inteira (filtradas pela fase)
                        Forms\Components\Select::make('subject_ids')
                            ->label('Disciplinas')
                            ->options(function ($get) {
                                $coursePhaseId = $get('course_phase_id');
                                $courseId = $get('course_id');
                                
                                // Se tiver fase selecionada, mostrar apenas disciplinas dessa fase
                                if ($coursePhaseId) {
                                    $subjects = Subject::where('course_phase_id', $coursePhaseId)->pluck('name', 'id');
                                    if ($subjects->count() > 0) {
                                        return $subjects;
                                    }
                                }
                                
                                // Se tiver apenas curso, mostrar disciplinas de todas as fases do curso
                                if ($courseId) {
                                    $coursePhaseIds = CoursePhase::where('course_id', $courseId)->pluck('id');
                                    if ($coursePhaseIds->count() > 0) {
                                        $subjects = Subject::whereIn('course_phase_id', $coursePhaseIds)->pluck('name', 'id');
                                        if ($subjects->count() > 0) {
                                            return $subjects;
                                        }
                                    }
                                }
                                
                                // Se não houver disciplinas, retornar vazio
                                return [];
                            })
                            ->multiple()
                            ->searchable()
                            ->columnSpanFull()
                            ->helperText('Disciplinas da fase selecionada'),
                    ])
                    ->action(function (array $data): void {
                        // Actualizar tipo do aluno e dados de localização
                        $student = Student::find($data['student_id']);
                        if ($student) {
                            $student->update([
                                'student_type' => $data['student_type'],
                                'enrollment_date' => now(),
                                'cia' => $data['cia'] ?? null,
                                'platoon' => $data['platoon'] ?? null,
                                'section' => $data['section'] ?? null,
                            ]);
                        }
                        
                        // Criar inscrição na turma
                        StudentClassEnrollment::updateOrCreate(
                            [
                                'student_id' => $data['student_id'],
                                'class_id' => $data['class_id'],
                                'course_phase_id' => $data['course_phase_id'] ?? null,
                            ],
                            [
                                'academic_year_id' => $data['academic_year_id'] ?? null,
                                'student_type' => $data['student_type'],
                                'classroom' => $data['classroom'] ?? null,
                                'is_active' => true,
                                'enrolled_at' => now(),
                                'enrolled_by' => auth()->id(),
                            ]
                        );
                        
                        // Inscrever nas disciplinas
                        $subjectIds = $data['subject_ids'] ?? [];
                        foreach ($subjectIds as $subjectId) {
                            StudentSubjectEnrollment::updateOrCreate(
                                [
                                    'student_id' => $data['student_id'],
                                    'subject_id' => $subjectId,
                                    'class_id' => $data['class_id'],
                                    'course_phase_id' => $data['course_phase_id'] ?? null,
                                ],
                                [
                                    'is_active' => true,
                                ]
                            );
                        }
                    })
                    ->modalSubmitAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-check')->label('Inscrever'))
                    ->modalCancelAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-x-mark')->label('Cancelar')->color('danger'))
                    ->successNotificationTitle('Aluno inscrito com sucesso!'),
            ])
            ->actions([
                \Filament\Actions\Action::make('visualizar')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn (Student $record) => 'Detalhes - ' . ($record->candidate?->full_name ?? 'N/A'))
                    ->modalWidth('3xl')
                    ->modalSubmitAction(false)
                    ->modalCancelAction(fn (\Filament\Actions\Action $action) => $action->label('Fechar')->icon('heroicon-o-x-mark')->color('danger'))
                    ->infolist(function (Student $record) {
                        return [
                            \Filament\Schemas\Components\Section::make('Dados Pessoais')
                                ->headerActions([
                                    \Filament\Actions\Action::make('imprimirFicha')
                                        ->label('Baixar Ficha PDF')
                                        ->icon('heroicon-o-arrow-down-tray')
                                        ->color('primary')
                                        ->url(fn () => route('student.print-ficha', ['student' => $record->id]))
                                        ->openUrlInNewTab(),
                                    \Filament\Actions\Action::make('moverAluno')
                                        ->label('Mover Aluno')
                                        ->icon('heroicon-o-arrow-right-circle')
                                        ->color('warning')
                                        ->requiresConfirmation()
                                        ->modalHeading('Mover Aluno para Outra Instituição')
                                        ->modalDescription(fn () => 'O aluno "' . ($record->candidate?->full_name ?? 'N/A') . '" será transferido para outra instituição mantendo todas as suas informações, inscrições e disciplinas.')
                                        ->modalIcon('heroicon-o-building-office')
                                        ->form([
                                            Forms\Components\Placeholder::make('current_institution')
                                                ->label('Instituição Actual')
                                                ->content(fn () => $record->institution?->name ?? 'Sem instituição definida'),
                                            Forms\Components\Select::make('new_institution_id')
                                                ->label('Nova Instituição')
                                                ->options(fn () => \App\Models\Institution::where('id', '!=', $record->institution_id)->pluck('name', 'id'))
                                                ->required()
                                                ->searchable()
                                                ->preload()
                                                ->helperText('Selecione a instituição de destino'),
                                        ])
                                        ->action(function (array $data) use ($record): void {
                                            $oldInstitution = $record->institution?->name ?? 'N/A';
                                            $newInstitution = \App\Models\Institution::find($data['new_institution_id'])?->name ?? 'N/A';
                                            
                                            // 1. Atualizar a instituição do aluno (Student)
                                            $record->update(['institution_id' => $data['new_institution_id']]);
                                            
                                            // 2. Atualizar o candidato associado (se existir)
                                            if ($record->candidate) {
                                                $record->candidate->update(['institution_id' => $data['new_institution_id']]);
                                            }
                                            
                                            // 3. As inscrições de turma e disciplinas permanecem intactas
                                            // pois estão vinculadas ao student_id, não à instituição
                                            
                                            \Filament\Notifications\Notification::make()
                                                ->title('Aluno Movido com Sucesso!')
                                                ->body("Transferido de \"{$oldInstitution}\" para \"{$newInstitution}\". Todas as inscrições e disciplinas foram mantidas.")
                                                ->success()
                                                ->duration(5000)
                                                ->send();
                                        })
                                        ->modalSubmitActionLabel('Confirmar Transferência')
                                        ->modalCancelActionLabel('Cancelar'),
                                ])
                                ->schema([
                                    \Filament\Infolists\Components\TextEntry::make('student_number')->label('Nº Aluno'),
                                    \Filament\Infolists\Components\TextEntry::make('candidate.full_name')->label('Nome Completo'),
                                    \Filament\Infolists\Components\TextEntry::make('candidate.bi_number')->label('Nº BI'),
                                    \Filament\Infolists\Components\TextEntry::make('candidate.phone')->label('Telefone'),
                                    \Filament\Infolists\Components\TextEntry::make('student_type')->label('Estado')->badge(),
                                    \Filament\Infolists\Components\TextEntry::make('institution.name')->label('Instituição'),
                                ])->columns(3),
                            \Filament\Schemas\Components\Section::make('Localização')
                                ->schema([
                                    \Filament\Infolists\Components\TextEntry::make('cia')
                                        ->label('CIA')
                                        ->formatStateUsing(fn ($state) => $state ? "{$state}ª CIA" : '-'),
                                    \Filament\Infolists\Components\TextEntry::make('platoon')
                                        ->label('Pelotão')
                                        ->formatStateUsing(fn ($state) => $state ? "{$state}º PELOTÃO" : '-'),
                                    \Filament\Infolists\Components\TextEntry::make('section')
                                        ->label('Secção')
                                        ->formatStateUsing(fn ($state) => $state ? "{$state}ª SECÇÃO" : '-'),
                                ])->columns(3),
                            \Filament\Schemas\Components\Section::make('Curso e Disciplinas')
                                ->schema([
                                    \Filament\Infolists\Components\TextEntry::make('classEnrollments')
                                        ->label('Curso')
                                        ->getStateUsing(fn () => $record->classEnrollments
                                            ->map(fn ($e) => $e->studentClass?->courseMap?->course?->name)
                                            ->filter()->unique()->implode(', ') ?: '-'),
                                    \Filament\Infolists\Components\TextEntry::make('subject_enrollments_count')
                                        ->label('Total de Disciplinas')
                                        ->badge()
                                        ->color('primary'),
                                ])->columns(2),
                        ];
                    }),
                \Filament\Actions\Action::make('editarInscricoes')
                    ->label('Editar')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->modalHeading(fn (Student $record) => 'Inscrições - ' . ($record->candidate->full_name ?? 'N/A'))
                    ->form(function (Student $record) {
                        return [
                            Forms\Components\Select::make('student_type')
                                ->label('Estado do Aluno')
                                ->options(fn () => StudentType::where('is_active', true)->orderBy('order')->pluck('name', 'name')->toArray())
                                ->default($record->student_type)
                                ->required(),
                        ];
                    })
                    ->action(function (Student $record, array $data): void {
                        $record->update(['student_type' => $data['student_type']]);
                    })
                    ->modalSubmitAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-check')->label('Salvar'))
                    ->modalCancelAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-x-mark')->label('Cancelar')->color('danger'))
                    ->successNotificationTitle('Inscrições actualizadas!'),
                \Filament\Actions\Action::make('adicionarDisciplinas')
                    ->label('Disciplinas')
                    ->icon('heroicon-o-academic-cap')
                    ->color('success')
                    ->form(function (Student $record) {
                        $lastEnrollment = $record->classEnrollments()->with('studentClass.courseMap.course.phases')->latest()->first();
                        
                        // Buscar o curso do aluno através da última inscrição
                        $courseId = $lastEnrollment?->studentClass?->courseMap?->course_id;
                        $courseName = $lastEnrollment?->studentClass?->courseMap?->course?->name ?? 'Não definido';
                        
                        // Buscar disciplinas já inscritas do aluno
                        $existingSubjectIds = $record->subjectEnrollments()
                            ->where('is_active', true)
                            ->pluck('subject_id')
                            ->toArray();
                        
                        // Buscar disciplinas do curso através das fases (CoursePhase)
                        // As disciplinas têm course_phase_id que liga às fases do curso
                        $courseSubjectOptions = collect();
                        
                        if ($courseId) {
                            // Buscar todas as fases deste curso
                            $coursePhaseIds = CoursePhase::where('course_id', $courseId)->pluck('id');
                            
                            if ($coursePhaseIds->count() > 0) {
                                // Buscar disciplinas dessas fases
                                $courseSubjectOptions = Subject::whereIn('course_phase_id', $coursePhaseIds)
                                    ->pluck('name', 'id');
                            }
                        }
                        
                        // Combinar disciplinas do curso + disciplinas que o aluno já tem
                        $existingSubjects = Subject::whereIn('id', $existingSubjectIds)->pluck('name', 'id');
                        $subjectOptions = $courseSubjectOptions->union($existingSubjects);
                        
                        // Se não houver disciplinas, mostrar todas como fallback
                        if ($subjectOptions->isEmpty()) {
                            $subjectOptions = Subject::pluck('name', 'id');
                        }
                        
                        // Buscar fases do curso para o dropdown
                        $coursePhaseOptions = collect();
                        if ($courseId) {
                            $coursePhaseOptions = CoursePhase::where('course_id', $courseId)->pluck('name', 'id');
                        }
                        if ($coursePhaseOptions->isEmpty()) {
                            $coursePhaseOptions = CoursePhase::pluck('name', 'id');
                        }
                        
                        return [
                            Forms\Components\Placeholder::make('curso_info')
                                ->label('Curso do Aluno')
                                ->content($courseName),
                            \Filament\Schemas\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\Select::make('class_id')
                                        ->label('Turma')
                                        ->options(fn () => StudentClass::pluck('name', 'id'))
                                        ->default($lastEnrollment?->class_id)
                                        ->required(),
                                    Forms\Components\Select::make('course_phase_id')
                                        ->label('Fase')
                                        ->options($coursePhaseOptions)
                                        ->default($lastEnrollment?->course_phase_id),
                                ]),
                            Forms\Components\Select::make('subject_ids')
                                ->label('Disciplinas')
                                ->options($subjectOptions)
                                ->default($existingSubjectIds)
                                ->multiple()
                                ->required()
                                ->searchable()
                                ->helperText('Disciplinas do curso: ' . $courseName),
                        ];
                    })
                    ->action(function (Student $record, array $data): void {
                        $subjectIds = $data['subject_ids'] ?? [];
                        
                        foreach ($subjectIds as $subjectId) {
                            StudentSubjectEnrollment::updateOrCreate(
                                [
                                    'student_id' => $record->id,
                                    'subject_id' => $subjectId,
                                    'class_id' => $data['class_id'],
                                    'course_phase_id' => $data['course_phase_id'] ?? null,
                                ],
                                [
                                    'is_active' => true,
                                ]
                            );
                        }
                    })
                    ->modalSubmitAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-check')->label('Adicionar')->color('primary'))
                    ->modalCancelAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-x-mark')->label('Cancelar')->color('danger'))
                    ->successNotificationTitle('Disciplinas adicionadas!'),
            ])
            ->bulkActions([
                // Bulk Action para Recrutas -> Instruendo
                \Filament\Actions\BulkAction::make('recrutaParaInstruendo')
                    ->label('Recrutas → Instruendo')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('info')
                    ->deselectRecordsAfterCompletion()
                    ->requiresConfirmation()
                    ->modalHeading('Promover Recrutas para Instruendo')
                    ->modalDescription('Os recrutas selecionados serão promovidos para Instruendo e as disciplinas do curso serão adicionadas automaticamente.')
                    ->modalIcon('heroicon-o-arrow-up-circle')
                    ->modalIconColor('info')
                    ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                        $newType = '2ª Fase - Instruendo';
                        $count = 0;
                        $skipped = 0;
                        
                        foreach ($records as $student) {
                            // Verificar se é Recruta - apenas recrutas podem ser promovidos aqui
                            if (!str_contains(strtolower($student->student_type ?? ''), 'recruta')) {
                                $skipped++;
                                continue;
                            }
                            
                            // Atualizar o tipo do estudante
                            $student->update(['student_type' => $newType]);
                            
                            // Obter a última inscrição de turma
                            $lastEnrollment = $student->classEnrollments()
                                ->with('studentClass.courseMap.course.phases')
                                ->latest()
                                ->first();
                            
                            if ($lastEnrollment) {
                                $courseId = $lastEnrollment->studentClass?->courseMap?->course_id;
                                $classId = $lastEnrollment->class_id;
                                
                                if ($courseId) {
                                    $coursePhaseIds = CoursePhase::where('course_id', $courseId)->pluck('id');
                                    $subjects = Subject::whereIn('course_phase_id', $coursePhaseIds)->get();
                                    
                                    foreach ($subjects as $subject) {
                                        StudentSubjectEnrollment::updateOrCreate(
                                            [
                                                'student_id' => $student->id,
                                                'subject_id' => $subject->id,
                                                'class_id' => $classId,
                                                'course_phase_id' => $subject->course_phase_id,
                                            ],
                                            ['is_active' => true]
                                        );
                                    }
                                }
                            }
                            
                            $count++;
                        }
                        
                        $msg = "$count recrutas promovidos para 2ª Fase - Instruendo";
                        if ($skipped > 0) {
                            $msg .= " ($skipped ignorados por não serem recrutas)";
                        }
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Promoção concluída!')
                            ->body($msg)
                            ->success()
                            ->send();
                    })
                    ->modalSubmitAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-check')->label('Promover')->color('primary'))
                    ->modalCancelAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-x-mark')->label('Cancelar')->color('danger')),
                
                // Bulk Action para Formandos Superiores -> Em Formação
                \Filament\Actions\BulkAction::make('formandoParaEmFormacao')
                    ->label('Formandos → Em Formação')
                    ->icon('heroicon-o-academic-cap')
                    ->color('gray')
                    ->extraAttributes([
                        'style' => 'background-color: #0d5442 !important; color: white !important; border-color: #0d5442 !important;',
                        'class' => 'formandos-btn',
                        'onmouseover' => "this.style.backgroundColor='#04de71'; this.style.borderColor='#04de71';",
                        'onmouseout' => "this.style.backgroundColor='#0d5442'; this.style.borderColor='#0d5442';",
                    ])
                    ->deselectRecordsAfterCompletion()
                    ->requiresConfirmation()
                    ->modalHeading('Iniciar Formação Superior')
                    ->modalDescription('Os formandos superiores selecionados serão promovidos para "Em Formação" e as disciplinas do curso serão adicionadas automaticamente.')
                    ->modalIcon('heroicon-o-academic-cap')
                    ->modalIconColor('success')
                    ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                        // Criar ou usar estado 'Em Formação Superior'
                        $emFormacao = StudentType::firstOrCreate(
                            ['name' => 'Em Formação Superior'],
                            ['color' => 'warning', 'description' => 'Agentes a frequentar curso superior', 'is_active' => true, 'order' => 5]
                        );
                        $newType = $emFormacao->name;
                        $count = 0;
                        $skipped = 0;
                        
                        foreach ($records as $student) {
                            // Verificar se é Formando Superior ou Agente
                            $currentType = strtolower($student->student_type ?? '');
                            $isFormandoSuperior = str_contains($currentType, 'formando') && str_contains($currentType, 'superior');
                            $isAgente = str_contains($currentType, 'agente');
                            
                            if (!$isFormandoSuperior && !$isAgente) {
                                $skipped++;
                                continue;
                            }
                            
                            // Atualizar o tipo do estudante
                            $student->update(['student_type' => $newType]);
                            
                            // Obter a última inscrição de turma
                            $lastEnrollment = $student->classEnrollments()
                                ->with('studentClass.courseMap.course.phases')
                                ->latest()
                                ->first();
                            
                            if ($lastEnrollment) {
                                $courseId = $lastEnrollment->studentClass?->courseMap?->course_id;
                                $classId = $lastEnrollment->class_id;
                                
                                if ($courseId) {
                                    $coursePhaseIds = CoursePhase::where('course_id', $courseId)->pluck('id');
                                    $subjects = Subject::whereIn('course_phase_id', $coursePhaseIds)->get();
                                    
                                    foreach ($subjects as $subject) {
                                        StudentSubjectEnrollment::updateOrCreate(
                                            [
                                                'student_id' => $student->id,
                                                'subject_id' => $subject->id,
                                                'class_id' => $classId,
                                                'course_phase_id' => $subject->course_phase_id,
                                            ],
                                            ['is_active' => true]
                                        );
                                    }
                                }
                            }
                            
                            $count++;
                        }
                        
                        $msg = "$count formandos promovidos para $newType";
                        if ($skipped > 0) {
                            $msg .= " ($skipped ignorados por não serem formandos superiores)";
                        }
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Formação iniciada!')
                            ->body($msg)
                            ->success()
                            ->send();
                    })
                    ->modalSubmitAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-check')->label('Iniciar Formação')->color('primary'))
                    ->modalCancelAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-x-mark')->label('Cancelar')->color('danger')),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudentClassEnrollments::route('/'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('ViewAny:StudentClassEnrollment') ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}
