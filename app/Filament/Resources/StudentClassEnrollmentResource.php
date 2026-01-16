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
            ->whereHas('classEnrollments') // Mostrar apenas alunos com inscrições
            ->with(['candidate', 'institution', 'currentPhase', 'classEnrollments.studentClass', 'classEnrollments.coursePhase'])
            ->withCount(['classEnrollments', 'subjectEnrollments']);
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
                            ->searchable(['student_number'])
                            ->preload(false),
                        Forms\Components\Select::make('student_type')
                            ->label('Tipo de Aluno')
                            ->options(fn () => self::getStudentTypeOptions())
                            ->required()
                            ->live(),
                        Forms\Components\Select::make('class_id')
                            ->label('Turma')
                            ->relationship('studentClass', 'name')
                            ->required()
                            ->searchable()
                            ->preload(false),
                        Forms\Components\Select::make('course_phase_id')
                            ->label('Fase do Curso')
                            ->relationship('coursePhase', 'name')
                            ->searchable()
                            ->preload(false),
                        Forms\Components\TextInput::make('classroom')
                            ->label('Sala de Aula')
                            ->maxLength(50),
                        Forms\Components\Select::make('academic_year_id')
                            ->label('Ano Académico')
                            ->options(AcademicYear::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('subject_ids')
                            ->label('Disciplinas')
                            ->options(Subject::pluck('name', 'id'))
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->columnSpan(2),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        $typeColors = self::getStudentTypeColors();
        
        return $table
            ->deferLoading()
            ->striped()
            ->defaultSort('created_at', 'desc')
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
                    ->label('Tipo')
                    ->badge()
                    ->color(fn ($state) => $typeColors[$state] ?? 'gray'),
                Tables\Columns\TextColumn::make('classEnrollments')
                    ->label('Turmas')
                    ->getStateUsing(fn (Student $record) => 
                        $record->classEnrollments
                            ->pluck('studentClass.name')
                            ->filter()
                            ->unique()
                            ->implode(', ')
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
                    ->label('Tipo de Aluno')
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
                    ->label('Nova Inscrição')
                    ->modalHeading('Inscrever Aluno em Turma e Disciplinas')
                    ->form([
                        \Filament\Schemas\Components\Wizard::make([
                            // Etapa 1 - Dados do Aluno
                            \Filament\Schemas\Components\Wizard\Step::make('Dados do Aluno')
                                ->icon('heroicon-o-user')
                                ->description('Seleccione o aluno')
                                ->schema([
                                    Forms\Components\Select::make('student_id')
                                        ->label('Aluno')
                                        ->options(function () {
                                            return Student::with(['candidate'])
                                                ->withCount('classEnrollments')
                                                ->limit(100)
                                                ->get()
                                                ->mapWithKeys(function ($student) {
                                                    $name = $student->candidate->full_name ?? 'N/A';
                                                    $number = $student->student_number;
                                                    $count = $student->class_enrollments_count;
                                                    $indicator = $count > 0 ? " ✓ ({$count})" : " ○";
                                                    return [$student->id => $name . ' - ' . $number . $indicator];
                                                });
                                        })
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->afterStateUpdated(function ($state, $set) {
                                            if ($state) {
                                                $student = Student::with('classEnrollments.studentClass')->find($state);
                                                if ($student) {
                                                    $set('student_type', $student->student_type);
                                                    $set('nip_display', $student->nuri);
                                                    $enrollments = $student->classEnrollments;
                                                    if ($enrollments->count() > 0) {
                                                        $classes = $enrollments->map(fn($e) => $e->studentClass->name ?? 'N/A')->implode(', ');
                                                        $set('enrollment_info', "⚠️ Já inscrito em: {$classes}");
                                                    } else {
                                                        $set('enrollment_info', '✅ Sem inscrições anteriores');
                                                    }
                                                }
                                            }
                                        })
                                        ->columnSpanFull(),
                                    // Informação de inscrições existentes
                                    Forms\Components\Placeholder::make('enrollment_info')
                                        ->label('Estado de Inscrição')
                                        ->content(fn ($get) => $get('enrollment_info') ?? 'Seleccione um aluno')
                                        ->columnSpanFull(),
                                    Forms\Components\Select::make('student_type')
                                        ->label('Tipo de Aluno')
                                        ->options(fn () => StudentType::where('is_active', true)->orderBy('order')->pluck('name', 'name')->toArray())
                                        ->required()
                                        ->live(),
                                    // Campo NIP para Agentes/Formando Superior (só leitura)
                                    Forms\Components\TextInput::make('nip_display')
                                        ->label('NIP')
                                        ->disabled()
                                        ->dehydrated(false)
                                        ->visible(fn ($get) => in_array($get('student_type'), ['Formando Superior', 'Agente']))
                                        ->helperText('Preenchido automaticamente'),
                                    // Campo NURI para outros tipos
                                    Forms\Components\TextInput::make('nuri')
                                        ->label('NURI')
                                        ->maxLength(50)
                                        ->visible(fn ($get) => !in_array($get('student_type'), ['Formando Superior', 'Agente'])),
                                ])->columns(2),
                            
                            // Etapa 2 - Unidade Militar
                            \Filament\Schemas\Components\Wizard\Step::make('Unidade Militar')
                                ->icon('heroicon-o-building-office')
                                ->description('Informação da unidade')
                                ->schema([
                                    Forms\Components\Select::make('cia')
                                        ->label('Companhia')
                                        ->options([
                                            '1ª Companhia' => '1ª Companhia',
                                            '2ª Companhia' => '2ª Companhia',
                                            '3ª Companhia' => '3ª Companhia',
                                            '4ª Companhia' => '4ª Companhia',
                                            '5ª Companhia' => '5ª Companhia',
                                            '6ª Companhia' => '6ª Companhia',
                                            '7ª Companhia' => '7ª Companhia',
                                            '8ª Companhia' => '8ª Companhia',
                                            '9ª Companhia' => '9ª Companhia',
                                            '10ª Companhia' => '10ª Companhia',
                                            '11ª Companhia' => '11ª Companhia',
                                            '12ª Companhia' => '12ª Companhia',
                                            '13ª Companhia' => '13ª Companhia',
                                            '14ª Companhia' => '14ª Companhia',
                                            '15ª Companhia' => '15ª Companhia',
                                            'Companhia de Comando' => 'Companhia de Comando',
                                            'Companhia de Apoio' => 'Companhia de Apoio',
                                        ])
                                        ->searchable()
                                        ->preload()
                                        ->required(),
                                    Forms\Components\Select::make('platoon')
                                        ->label('Pelotão')
                                        ->options([
                                            '1º Pelotão' => '1º Pelotão',
                                            '2º Pelotão' => '2º Pelotão',
                                            '3º Pelotão' => '3º Pelotão',
                                            '4º Pelotão' => '4º Pelotão',
                                            '5º Pelotão' => '5º Pelotão',
                                            '6º Pelotão' => '6º Pelotão',
                                            '7º Pelotão' => '7º Pelotão',
                                            '8º Pelotão' => '8º Pelotão',
                                            '9º Pelotão' => '9º Pelotão',
                                            '10º Pelotão' => '10º Pelotão',
                                            '11º Pelotão' => '11º Pelotão',
                                            '12º Pelotão' => '12º Pelotão',
                                            '13º Pelotão' => '13º Pelotão',
                                            '14º Pelotão' => '14º Pelotão',
                                            '15º Pelotão' => '15º Pelotão',
                                        ])
                                        ->searchable()
                                        ->preload()
                                        ->required(),
                                    Forms\Components\Select::make('section')
                                        ->label('Secção')
                                        ->options([
                                            '1ª Secção' => '1ª Secção',
                                            '2ª Secção' => '2ª Secção',
                                            '3ª Secção' => '3ª Secção',
                                            '4ª Secção' => '4ª Secção',
                                            '5ª Secção' => '5ª Secção',
                                            '6ª Secção' => '6ª Secção',
                                            '7ª Secção' => '7ª Secção',
                                            '8ª Secção' => '8ª Secção',
                                            '9ª Secção' => '9ª Secção',
                                            '10ª Secção' => '10ª Secção',
                                            '11ª Secção' => '11ª Secção',
                                            '12ª Secção' => '12ª Secção',
                                            '13ª Secção' => '13ª Secção',
                                            '14ª Secção' => '14ª Secção',
                                            '15ª Secção' => '15ª Secção',
                                        ])
                                        ->searchable()
                                        ->preload()
                                        ->required(),
                                ])->columns(3),
                            
                            // Etapa 3 - Informação Académica
                            \Filament\Schemas\Components\Wizard\Step::make('Informação Académica')
                                ->icon('heroicon-o-academic-cap')
                                ->description('Turma e dados académicos')
                                ->schema([
                                    Forms\Components\Select::make('class_id')
                                        ->label('Turma')
                                        ->options(
                                            StudentClass::with('institution')
                                                ->get()
                                                ->mapWithKeys(fn ($class) => [$class->id => $class->name . ' - ' . ($class->institution->name ?? '')])
                                        )
                                        ->required()
                                        ->searchable()
                                        ->preload(),
                                    Forms\Components\Select::make('course_phase_id')
                                        ->label('Fase do Curso')
                                        ->options(CoursePhase::pluck('name', 'id'))
                                        ->searchable()
                                        ->preload()
                                        ->required(),
                                    Forms\Components\TextInput::make('classroom')
                                        ->label('Sala de Aula')
                                        ->maxLength(50)
                                        ->required(),
                                    Forms\Components\Select::make('academic_year_id')
                                        ->label('Ano Académico')
                                        ->options(AcademicYear::where('is_active', true)->pluck('name', 'id'))
                                        ->searchable()
                                        ->preload()
                                        ->required(),
                                    Forms\Components\DatePicker::make('enrollment_date')
                                        ->label('Data de Matrícula')
                                        ->default(now())
                                        ->required(),
                                ])->columns(2),
                            
                            // Etapa 4 - Disciplinas
                            \Filament\Schemas\Components\Wizard\Step::make('Disciplinas')
                                ->icon('heroicon-o-book-open')
                                ->description('Seleccione as disciplinas')
                                ->schema([
                                    Forms\Components\Select::make('subject_ids')
                                        ->label('Seleccione as Disciplinas')
                                        ->options(Subject::pluck('name', 'id'))
                                        ->multiple()
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->columnSpanFull(),
                                ]),
                        ])->columnSpanFull(),
                    ])
                    ->action(function (array $data): void {
                        // Actualizar dados do aluno
                        $student = Student::find($data['student_id']);
                        if ($student) {
                            $student->update([
                                'student_type' => $data['student_type'],
                                'nuri' => $data['nuri'] ?? null,
                                'cia' => $data['cia'] ?? null,
                                'platoon' => $data['platoon'] ?? null,
                                'section' => $data['section'] ?? null,
                                'enrollment_date' => $data['enrollment_date'] ?? now(),
                            ]);
                        }
                        
                        // Criar inscrição na turma
                        $enrollment = StudentClassEnrollment::updateOrCreate(
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
                        $enrollments = $record->classEnrollments()->with(['studentClass', 'coursePhase'])->get();
                        
                        return [
                            Forms\Components\Select::make('student_type')
                                ->label('Tipo de Aluno')
                                ->options(fn () => StudentType::where('is_active', true)->orderBy('order')->pluck('name', 'name')->toArray())
                                ->default($record->student_type)
                                ->required(),
                            Forms\Components\Repeater::make('enrollments')
                                ->label('Inscrições em Turmas')
                                ->schema([
                                    Forms\Components\Select::make('class_id')
                                        ->label('Turma')
                                        ->options(
                                            StudentClass::with('institution')
                                                ->get()
                                                ->mapWithKeys(fn ($class) => [$class->id => $class->name . ' - ' . ($class->institution->name ?? '')])
                                        )
                                        ->required()
                                        ->searchable()
                                        ->preload(),
                                    Forms\Components\Select::make('course_phase_id')
                                        ->label('Fase')
                                        ->options(CoursePhase::pluck('name', 'id'))
                                        ->searchable()
                                        ->preload(),
                                    Forms\Components\TextInput::make('classroom')
                                        ->label('Sala')
                                        ->maxLength(50),
                                    Forms\Components\Toggle::make('is_active')
                                        ->label('Activo')
                                        ->default(true),
                                ])
                                ->columns(4)
                                ->default($enrollments->map(fn ($e) => [
                                    'class_id' => $e->class_id,
                                    'course_phase_id' => $e->course_phase_id,
                                    'classroom' => $e->classroom,
                                    'is_active' => $e->is_active,
                                ])->toArray())
                                ->addActionLabel('Adicionar Turma')
                                ->reorderable(false),
                        ];
                    })
                    ->action(function (Student $record, array $data): void {
                        // Actualizar tipo do aluno
                        $record->update(['student_type' => $data['student_type']]);
                        
                        // Remover inscrições antigas
                        $record->classEnrollments()->delete();
                        
                        // Criar novas inscrições
                        foreach ($data['enrollments'] ?? [] as $enrollment) {
                            StudentClassEnrollment::create([
                                'student_id' => $record->id,
                                'class_id' => $enrollment['class_id'],
                                'course_phase_id' => $enrollment['course_phase_id'] ?? null,
                                'student_type' => $data['student_type'],
                                'classroom' => $enrollment['classroom'] ?? null,
                                'is_active' => $enrollment['is_active'] ?? true,
                                'enrolled_at' => now(),
                                'enrolled_by' => auth()->id(),
                            ]);
                        }
                    })
                    ->modalSubmitAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-check')->label('Salvar'))
                    ->modalCancelAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-x-mark')->label('Cancelar')->color('danger'))
                    ->successNotificationTitle('Inscrições actualizadas!'),
                \Filament\Actions\Action::make('verDetalhes')
                    ->label('Detalhes')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->modalHeading(fn (Student $record) => 'Detalhes - ' . ($record->candidate->full_name ?? 'N/A'))
                    ->modalContent(fn (Student $record) => view('filament.resources.student-class-enrollment-resource.modal-details', [
                        'student' => $record,
                        'enrollments' => $record->classEnrollments()->with(['studentClass', 'coursePhase'])->get(),
                        'subjects' => $record->subjectEnrollments()->with(['subject', 'studentClass', 'coursePhase'])->get(),
                    ]))
                    ->modalSubmitAction(false),
                \Filament\Actions\Action::make('adicionarDisciplinas')
                    ->label('Disciplinas')
                    ->icon('heroicon-o-academic-cap')
                    ->color('success')
                    ->form(function (Student $record) {
                        $enrollments = $record->classEnrollments()->with(['studentClass', 'coursePhase'])->get();
                        $classOptions = $enrollments->mapWithKeys(fn ($e) => [$e->class_id => $e->studentClass->name ?? 'N/A']);
                        
                        // Pegar a última inscrição para preencher automaticamente
                        $lastEnrollment = $enrollments->last();
                        $defaultClassId = $lastEnrollment?->class_id;
                        $defaultPhaseId = $lastEnrollment?->course_phase_id;
                        
                        return [
                            Forms\Components\Select::make('class_id')
                                ->label('Turma')
                                ->options($classOptions->toArray())
                                ->default($defaultClassId)
                                ->disabled()
                                ->dehydrated(true)
                                ->required(),
                            Forms\Components\Select::make('course_phase_id')
                                ->label('Fase')
                                ->options(CoursePhase::pluck('name', 'id'))
                                ->default($defaultPhaseId)
                                ->disabled()
                                ->dehydrated(true),
                            Forms\Components\Select::make('subject_ids')
                                ->label('Disciplinas')
                                ->options(Subject::pluck('name', 'id'))
                                ->multiple()
                                ->required()
                                ->searchable()
                                ->preload(),
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
