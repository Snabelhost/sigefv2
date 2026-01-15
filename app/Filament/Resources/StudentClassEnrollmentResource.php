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
    protected static ?string $navigationLabel = 'Inscrições de Alunos';
    protected static ?string $modelLabel = 'Inscrição';
    protected static ?string $pluralModelLabel = 'Inscrições de Alunos';
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
            ->with(['candidate', 'institution', 'currentPhase', 'classEnrollments.studentClass', 'classEnrollments.coursePhase'])
            ->withCount('classEnrollments');
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
                            ->options(
                                Student::with('candidate')
                                    ->get()
                                    ->mapWithKeys(fn ($student) => [
                                        $student->id => ($student->candidate->full_name ?? 'N/A') . ' - ' . $student->student_number
                                    ])
                            )
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('student_type')
                            ->label('Tipo de Aluno')
                            ->options(fn () => self::getStudentTypeOptions())
                            ->required()
                            ->live(),
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
                            ->preload(),
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
                Tables\Columns\TextColumn::make('institution.name')
                    ->label('Instituição')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('currentPhase.name')
                    ->label('Fase Actual')
                    ->sortable()
                    ->placeholder('-'),
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
                Tables\Columns\TextColumn::make('class_enrollments_count')
                    ->label('Inscrições')
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
                        Forms\Components\Select::make('student_id')
                            ->label('Aluno')
                            ->options(
                                Student::with('candidate')
                                    ->get()
                                    ->mapWithKeys(fn ($student) => [
                                        $student->id => ($student->candidate->full_name ?? 'N/A') . ' - ' . $student->student_number
                                    ])
                            )
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('student_type')
                            ->label('Tipo de Aluno')
                            ->options(fn () => StudentType::where('is_active', true)->orderBy('order')->pluck('name', 'name')->toArray())
                            ->required()
                            ->live(),
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
                            ->preload(),
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
                            ->preload(),
                    ])
                    ->action(function (array $data): void {
                        // Actualizar tipo do aluno
                        $student = Student::find($data['student_id']);
                        if ($student) {
                            $student->update(['student_type' => $data['student_type']]);
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
