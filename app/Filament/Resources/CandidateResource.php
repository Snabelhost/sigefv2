<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CandidateResource\Pages;
use App\Filament\Resources\CandidateResource\RelationManagers;
use App\Models\Candidate;
use App\Models\StudentType;
use App\Services\SmsService;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CandidateResource extends Resource
{
    protected static ?string $model = Candidate::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-s-user-plus';
    protected static string|\UnitEnum|null $navigationGroup = 'Gestão Escolar';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'Alistados';
    protected static ?string $modelLabel = 'Alistado';
    protected static ?string $pluralModelLabel = 'Alistados';

    /**
     * Badge com total de alistados (cadastrados neste formulário)
     */
    public static function getNavigationBadge(): ?string
    {
        return (string) \App\Models\Candidate::where('student_type', 'Alistado')->count();
    }

    /**
     * Cor do badge
     */
    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    // Filtrar apenas alistados cadastrados neste formulário
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('student_type', 'Alistado')
            ->with(['recruitmentType', 'academicYear']);
    }

    /**
     * Obter opções de tipos de aluno dinâmicas
     */
    public static function getStudentTypeOptions(): array
    {
        return StudentType::where('is_active', true)
            ->orderBy('order')
            ->pluck('name', 'name')
            ->toArray();
    }

    /**
     * Obter cores de tipos de aluno
     */
    public static function getStudentTypeColors(): array
    {
        return StudentType::where('is_active', true)
            ->pluck('color', 'name')
            ->toArray();
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Wizard::make([
                    // Etapa 1 - Modo de Cadastro
                    \Filament\Schemas\Components\Wizard\Step::make('Modo de Cadastro')
                        ->icon('heroicon-o-cog-6-tooth')
                        ->description('Escolha como deseja cadastrar')
                        ->schema([
                            Forms\Components\Radio::make('cadastro_mode')
                                ->label('Seleccione o Modo de Cadastro')
                                ->options([
                                    'automatico' => 'Automático - Buscar por API (BI)',
                                    'manual' => 'Manual - Preencher todos os dados',
                                ])
                                ->default('manual')
                                ->afterStateHydrated(fn ($component, $state) => $component->state($state ?? 'manual'))
                                ->dehydrated(false) // Não salvar este campo na base de dados
                                ->live()
                                ->columnSpanFull(),
                            
                            // Modo Automático - Buscar por BI
                            Forms\Components\TextInput::make('search_bi')
                                ->label('Número do Bilhete de Identidade')
                                ->visible(fn ($get) => $get('cadastro_mode') === 'automatico')
                                ->helperText('Digite o número do BI para buscar os dados automaticamente')
                                ->suffixAction(
                                    \Filament\Actions\Action::make('buscar')
                                        ->icon('heroicon-o-magnifying-glass')
                                        ->action(function ($state, $set) {
                                            // TODO: Implementar chamada à API
                                            // Por enquanto, simular dados
                                            if ($state) {
                                                $set('full_name', 'Dados da API - ' . $state);
                                                $set('id_number', $state);
                                            }
                                        })
                                )
                                ->columnSpanFull(),
                            
                            Forms\Components\Placeholder::make('api_info')
                                ->label('')
                                ->content('Após buscar, os dados serão preenchidos automaticamente.')
                                ->visible(fn ($get) => $get('cadastro_mode') === 'automatico'),
                        ]),

                    // Etapa 2 - Identificação Pessoal
                    \Filament\Schemas\Components\Wizard\Step::make('Identificação Pessoal')
                        ->icon('heroicon-o-user')
                        ->description('Dados pessoais do alistado')
                        ->schema([
                            Forms\Components\FileUpload::make('photo')
                                ->label('Foto')
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                                ->directory('candidates')
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('full_name')
                                ->label('Nome Completo')
                                ->required()
                                ->maxLength(191)
                                ->unique(ignoreRecord: true)
                                ->validationMessages([
                                    'unique' => 'Já existe um alistado com este nome.',
                                ]),
                            Forms\Components\Select::make('institution_id')
                                ->label('Escola de Formação')
                                ->options(function () {
                                    // Mostrar todas as instituições
                                    return \App\Models\Institution::orderBy('name')->pluck('name', 'id');
                                })
                                ->searchable()
                                ->preload()
                                ->required()
                                ->helperText('Seleccione a escola onde o alistado será formado'),
                            Forms\Components\TextInput::make('student_number')
                                ->label('Nº de Ordem')
                                ->maxLength(50)
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->validationMessages([
                                    'unique' => 'Já existe um candidato com este Nº de Ordem.',
                                ]),
                            Forms\Components\TextInput::make('id_number')
                                ->label('Nº do BI')
                                ->unique(ignoreRecord: true)
                                ->required()
                                ->maxLength(191)
                                ->validationMessages([
                                    'unique' => 'Já existe um candidato com este Nº de BI.',
                                ]),
                            Forms\Components\DatePicker::make('birth_date')
                                ->label('Data de Nascimento')
                                ->required(),
                            Forms\Components\Select::make('gender')
                                ->label('Género')
                                ->options([
                                    'Masculino' => 'Masculino',
                                    'Feminino' => 'Feminino',
                                ])
                                ->required(),
                            Forms\Components\Select::make('marital_status')
                                ->label('Estado Civil')
                                ->options([
                                    'solteiro' => 'Solteiro(a)',
                                    'casado' => 'Casado(a)',
                                    'divorciado' => 'Divorciado(a)',
                                    'viuvo' => 'Viúvo(a)',
                                ])
                                ->required(),
                            Forms\Components\TextInput::make('father_name')
                                ->label('Nome do Pai')
                                ->maxLength(191),
                            Forms\Components\TextInput::make('mother_name')
                                ->label('Nome da Mãe')
                                ->maxLength(191),
                        ])->columns(2),

                    // Etapa 3 - Localização e Contacto
                    \Filament\Schemas\Components\Wizard\Step::make('Localização e Contacto')
                        ->icon('heroicon-o-map-pin')
                        ->description('Endereço e contactos')
                        ->schema([
                            Forms\Components\Select::make('province_id')
                                ->label('Província')
                                ->options(\App\Models\Province::orderBy('name')->pluck('name', 'id'))
                                ->searchable()
                                ->preload()
                                ->live()
                                ->required()
                                ->afterStateUpdated(fn ($set) => $set('municipality_id', null)),
                            Forms\Components\Select::make('municipality_id')
                                ->label('Município')
                                ->options(function ($get) {
                                    $provinceId = $get('province_id');
                                    if (!$provinceId) {
                                        return [];
                                    }
                                    return \App\Models\Municipality::where('province_id', $provinceId)
                                        ->orderBy('name')
                                        ->pluck('name', 'id');
                                })
                                ->searchable()
                                ->preload()
                                ->required(),
                            Forms\Components\Textarea::make('address')
                                ->label('Endereço')
                                ->rows(2)
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('phone')
                                ->label('Telefone')
                                ->tel()
                                ->prefix('+244')
                                ->placeholder('9XX XXX XXX')
                                ->mask('999 999 999')
                                ->maxLength(191)
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->validationMessages([
                                    'unique' => 'Já existe um candidato com este número de telefone.',
                                ]),
                            Forms\Components\TextInput::make('email')
                                ->label('E-mail')
                                ->email()
                                ->maxLength(191)
                                ->unique(ignoreRecord: true)
                                ->validationMessages([
                                    'unique' => 'Já existe um candidato com este e-mail.',
                                ]),
                        ])->columns(2),

                    // Etapa 4 - Habilitações
                    \Filament\Schemas\Components\Wizard\Step::make('Habilitações')
                        ->icon('heroicon-o-academic-cap')
                        ->description('Formação académica')
                        ->schema([
                            Forms\Components\Select::make('education_level')
                                ->label('Nível Académico')
                                ->options([
                                    'Ensino Primário' => 'Ensino Primário (1ª - 6ª Classe)',
                                    '7ª Classe' => '7ª Classe',
                                    '8ª Classe' => '8ª Classe',
                                    '9ª Classe' => '9ª Classe (I Ciclo)',
                                    '10ª Classe' => '10ª Classe',
                                    '11ª Classe' => '11ª Classe',
                                    '12ª Classe' => '12ª Classe (II Ciclo)',
                                    '13ª Classe' => '13ª Classe',
                                    'Técnico Médio' => 'Técnico Médio',
                                    'Técnico Profissional' => 'Técnico Profissional',
                                    'Bacharelato' => 'Bacharelato',
                                    'Licenciatura' => 'Licenciatura',
                                    'Pós-Graduação' => 'Pós-Graduação',
                                    'Mestrado' => 'Mestrado',
                                    'Doutoramento' => 'Doutoramento',
                                ])
                                ->searchable()
                                ->preload()
                                ->required(),
                            Forms\Components\Select::make('education_area')
                                ->label('Área de Formação')
                                ->options([
                                    'Administração e Gestão' => 'Administração e Gestão',
                                    'Agricultura e Pecuária' => 'Agricultura e Pecuária',
                                    'Arquitectura e Urbanismo' => 'Arquitectura e Urbanismo',
                                    'Artes e Design' => 'Artes e Design',
                                    'Biologia e Ciências da Vida' => 'Biologia e Ciências da Vida',
                                    'Ciências da Comunicação' => 'Ciências da Comunicação',
                                    'Ciências da Educação' => 'Ciências da Educação',
                                    'Ciências Jurídicas' => 'Ciências Jurídicas / Direito',
                                    'Ciências Policiais' => 'Ciências Policiais',
                                    'Ciências Políticas' => 'Ciências Políticas',
                                    'Ciências Sociais' => 'Ciências Sociais',
                                    'Contabilidade e Auditoria' => 'Contabilidade e Auditoria',
                                    'Economia' => 'Economia',
                                    'Electrónica e Telecomunicações' => 'Electrónica e Telecomunicações',
                                    'Enfermagem' => 'Enfermagem',
                                    'Engenharia Civil' => 'Engenharia Civil',
                                    'Engenharia Electrotécnica' => 'Engenharia Electrotécnica',
                                    'Engenharia Informática' => 'Engenharia Informática',
                                    'Engenharia Mecânica' => 'Engenharia Mecânica',
                                    'Farmácia' => 'Farmácia',
                                    'Filosofia' => 'Filosofia',
                                    'Física' => 'Física',
                                    'Geografia' => 'Geografia',
                                    'Geologia e Minas' => 'Geologia e Minas',
                                    'Gestão de Recursos Humanos' => 'Gestão de Recursos Humanos',
                                    'História' => 'História',
                                    'Hotelaria e Turismo' => 'Hotelaria e Turismo',
                                    'Informática' => 'Informática',
                                    'Letras e Linguística' => 'Letras e Linguística',
                                    'Marketing' => 'Marketing',
                                    'Matemática' => 'Matemática',
                                    'Medicina' => 'Medicina',
                                    'Petróleos e Gás' => 'Petróleos e Gás',
                                    'Psicologia' => 'Psicologia',
                                    'Química' => 'Química',
                                    'Relações Internacionais' => 'Relações Internacionais',
                                    'Saúde Pública' => 'Saúde Pública',
                                    'Secretariado Executivo' => 'Secretariado Executivo',
                                    'Serviço Social' => 'Serviço Social',
                                    'Sociologia' => 'Sociologia',
                                    'Tecnologias de Informação' => 'Tecnologias de Informação',
                                    'Veterinária' => 'Veterinária',
                                    'Outra' => 'Outra',
                                ])
                                ->searchable()
                                ->preload(),
                            Forms\Components\Select::make('recruitment_type_id')
                                ->label('Tipo de Recrutamento')
                                ->relationship('recruitmentType', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                            Forms\Components\Select::make('academic_year_id')
                                ->label('Ano Académico')
                                ->options(\App\Models\AcademicYear::where('is_active', true)->pluck('year', 'id'))
                                ->default(fn () => \App\Models\AcademicYear::where('is_active', true)->first()?->id)
                                ->required(),
                            
                            // Tipo de Aluno definido automaticamente como Alistado
                            Forms\Components\Hidden::make('student_type')
                                ->default('Alistado'),
                        ])->columns(2),

                    // Etapa 5 - Documentos
                    \Filament\Schemas\Components\Wizard\Step::make('Documentos')
                        ->icon('heroicon-o-document-text')
                        ->description('Upload de documentos')
                        ->schema([
                            Forms\Components\FileUpload::make('bilhete_identidade')
                                ->label('Bilhete de Identidade')
                                ->directory('candidates/documents/bi'),
                            Forms\Components\FileUpload::make('certificado_doc')
                                ->label('Certificado')
                                ->directory('candidates/documents/certificados'),
                            Forms\Components\FileUpload::make('curriculum')
                                ->label('Curriculum')
                                ->directory('candidates/documents/curriculum'),
                            Forms\Components\FileUpload::make('registro_criminal')
                                ->label('Registo Criminal')
                                ->directory('candidates/documents/registro_criminal'),
                        ])->columns(2),
                ])
                ->columnSpanFull(),
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
                Tables\Columns\ImageColumn::make('photo')
                    ->label('Foto')
                    ->circular()
                    ->size(40)
                    ->defaultImageUrl(function ($record) {
                        $name = $record->full_name ?? 'Alistado';
                        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=0D47A1&color=fff&size=128&bold=true';
                    }),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nome Completo')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('id_number')
                    ->label('Nº BI')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefone')
                    ->searchable()
                    ->icon('heroicon-o-phone'),
                Tables\Columns\TextColumn::make('gender')
                    ->label('Género')
                    ->formatStateUsing(fn ($state) => $state === 'M' ? 'Masculino' : ($state === 'F' ? 'Feminino' : $state)),
                Tables\Columns\TextColumn::make('nuri')
                    ->label('NURI')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('student_type')
                    ->label('Estado')
                    ->badge()
                    ->color(fn ($state) => $typeColors[$state] ?? 'primary'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data Registo')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('student_type')
                    ->label('Tipo de Aluno')
                    ->options(fn () => self::getStudentTypeOptions()),
            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->modalSubmitAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-check')->label('Criar'))
                    ->modalCancelAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-x-mark')->label('Cancelar')->color('danger'))
                    ->createAnotherAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-plus-circle')->label('Salvar e criar outro'))
                    ->createAnother(true)
                    ->successNotificationTitle('Alistado criado com sucesso!')
                    ->after(function (Candidate $record) {
                        // Enviar SMS ao alistado após criar
                        $phone = $record->phone;
                        
                        if (!empty($phone)) {
                            $candidateName = $record->full_name ?? 'Alistado';
                            
                            // Buscar nome da instituição selecionada
                            $institutionName = 'Escola de Formacao da Policia Nacional';
                            if ($record->institution_id) {
                                $record->loadMissing('institution');
                                if ($record->institution) {
                                    $institutionName = $record->institution->name;
                                }
                            }
                            
                            // Remover acentos do nome da instituição
                            $institutionName = strtr($institutionName, [
                                'ã' => 'a', 'á' => 'a', 'à' => 'a', 'â' => 'a',
                                'é' => 'e', 'ê' => 'e', 'í' => 'i', 'ó' => 'o',
                                'ô' => 'o', 'õ' => 'o', 'ú' => 'u', 'ç' => 'c',
                                'Ã' => 'A', 'Á' => 'A', 'À' => 'A', 'Â' => 'A',
                                'É' => 'E', 'Ê' => 'E', 'Í' => 'I', 'Ó' => 'O',
                                'Ô' => 'O', 'Õ' => 'O', 'Ú' => 'U', 'Ç' => 'C',
                            ]);
                            
                            // Remover acentos do nome do candidato
                            $candidateName = strtr($candidateName, [
                                'ã' => 'a', 'á' => 'a', 'à' => 'a', 'â' => 'a',
                                'é' => 'e', 'ê' => 'e', 'í' => 'i', 'ó' => 'o',
                                'ô' => 'o', 'õ' => 'o', 'ú' => 'u', 'ç' => 'c',
                                'Ã' => 'A', 'Á' => 'A', 'À' => 'A', 'Â' => 'A',
                                'É' => 'E', 'Ê' => 'E', 'Í' => 'I', 'Ó' => 'O',
                                'Ô' => 'O', 'Õ' => 'O', 'Ú' => 'U', 'Ç' => 'C',
                            ]);
                            
                            try {
                                $smsService = new SmsService();
                                $result = $smsService->sendAgentRegistrationNotification(
                                    $phone,
                                    $candidateName,
                                    $institutionName
                                );
                                
                                if ($result['success']) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('SMS enviado')
                                        ->body("Notificacao enviada para {$phone}")
                                        ->success()
                                        ->send();
                                } else {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Falha ao enviar SMS')
                                        ->body("Nao foi possivel enviar SMS. Detalhes: " . ($result['message'] ?? 'Erro desconhecido'))
                                        ->warning()
                                        ->send();
                                }
                            } catch (\Exception $e) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Erro ao enviar SMS')
                                    ->body("Erro: " . $e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }
                    }),
            ])
            ->actions([
                \Filament\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil-square')
                    ->modalSubmitAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-check')->label('Salvar'))
                    ->modalCancelAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-x-mark')->label('Cancelar')->color('danger'))
                    ->successNotificationTitle('Alistado atualizado com sucesso!'),
                \Filament\Actions\DeleteAction::make()->icon('heroicon-o-trash'),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListCandidates::route('/'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('ViewAny:Candidate') ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}
