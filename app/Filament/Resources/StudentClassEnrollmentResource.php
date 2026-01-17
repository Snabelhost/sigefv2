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
                    ->sortable(),
                Tables\Columns\TextColumn::make('candidate.full_name')
                    ->label('Nome do Aluno')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('student_type')
                    ->label('Estado')
                    ->badge()
                    ->color(fn ($state) => $typeColors[$state] ?? 'gray'),
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
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('subject_enrollments_count')
                    ->label('Total de Disciplinas')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
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
                                    
                                    // Auto-preencher Estado baseado no tipo do candidato
                                    if ($candidateType === 'Agente') {
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
                        // Actualizar tipo do aluno
                        $student = Student::find($data['student_id']);
                        if ($student) {
                            $student->update([
                                'student_type' => $data['student_type'],
                                'enrollment_date' => now(),
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
                    ->modalSubmitAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-check')->label('Adicionar'))
                    ->modalCancelAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-x-mark')->label('Cancelar')->color('danger'))
                    ->successNotificationTitle('Disciplinas adicionadas!'),
            ])
            ->bulkActions([
                //
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
}
