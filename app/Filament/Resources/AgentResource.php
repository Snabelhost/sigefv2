<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AgentResource\Pages;
use App\Models\Student;
use App\Models\Candidate;
use App\Models\Institution;
use App\Models\Provenance;
use App\Models\Rank;
use App\Models\StudentType;
use App\Services\SmsService;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;


class AgentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-s-shield-check';
    protected static string|\UnitEnum|null $navigationGroup = 'Gestão Escolar';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Agentes';
    protected static ?string $modelLabel = 'Agente';
    protected static ?string $pluralModelLabel = 'Agentes';

    /**
     * Badge com total de agentes do Curso Superior
     */
    public static function getNavigationBadge(): ?string
    {
        return (string) Student::whereIn('status', ['em_formacao', 'concluiu'])
            ->where('student_type', 'Formando Superior')
            ->count();
    }

    /**
     * Cor do badge
     */
    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    // Filtrar agentes (Em Formação ou Formação Concluída) que estão no Curso Superior
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('status', ['em_formacao', 'concluiu'])
            ->where('student_type', 'Formando Superior')
            ->with(['candidate', 'institution', 'currentPhase', 'provenance', 'rank']);
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Tabs::make('Cadastro de Agente')
                    ->tabs([
                        \Filament\Schemas\Components\Tabs\Tab::make('Modo de Cadastro')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->hiddenOn('edit') // Esconder na edição
                            ->schema([
                                Forms\Components\Radio::make('cadastro_mode')
                                    ->label('Seleccione o Modo de Cadastro')
                                    ->options([
                                        'automatico' => 'Automático - Buscar por NIP',
                                        'manual' => 'Manual - Preencher todos os dados',
                                    ])
                                    ->default('automatico')
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set) {
                                        $set('candidate_id', null);
                                    })
                                    ->columnSpanFull(),

                                // Modo Automático - Buscar por NIP
                                Forms\Components\TextInput::make('nuri')
                                    ->label('NIP (Número de Identificação Policial)')
                                    ->visible(fn ($get) => $get('cadastro_mode') === 'automatico')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, $set) {
                                        if ($state) {
                                            $candidate = Candidate::where('id_number', $state)->first();
                                            if ($candidate) {
                                                $set('candidate_id', $candidate->id);
                                                $set('candidate_name_display', $candidate->full_name);
                                                $set('full_name_manual', $candidate->full_name);
                                                $set('institution_id', $candidate->institution_id);
                                                $set('provenance_id', $candidate->provenance_id);
                                                $set('rank_id', $candidate->current_rank_id);
                                            }
                                        }
                                    })
                                    ->helperText('Digite o NIP para preencher automaticamente: Nome e Patente')
                                    ->columnSpanFull(),

                                // Campo de exibição do nome encontrado
                                Forms\Components\Placeholder::make('candidate_name_display')
                                    ->label('Nome do Candidato Encontrado')
                                    ->content(fn ($get) => $get('candidate_name_display') ?? 'Nenhum candidato encontrado')
                                    ->visible(fn ($get) => $get('cadastro_mode') === 'automatico' && $get('nuri'))
                                    ->columnSpanFull(),

                                // Campo oculto para guardar o candidate_id
                                Forms\Components\Hidden::make('candidate_id'),
                            ]),

                        \Filament\Schemas\Components\Tabs\Tab::make('Dados Pessoais')
                            ->icon('heroicon-o-user')
                            ->schema([
                                // Campo Nome - preencher com nome do candidato na edição
                                Forms\Components\TextInput::make('full_name_manual')
                                    ->label('Nome Completo')
                                    ->required()
                                    ->maxLength(191)
                                    ->dehydrated(true)
                                    ->default(fn ($record) => $record?->candidate?->full_name)
                                    ->afterStateHydrated(function ($state, $set, $record) {
                                        if ($record && !$state) {
                                            $set('full_name_manual', $record->candidate?->full_name);
                                        }
                                    })
                                    ->helperText(fn ($get) => $get('cadastro_mode') === 'automatico' 
                                        ? 'Preenchido automaticamente pelo NIP' 
                                        : 'Digite o nome completo do agente'),

                                Forms\Components\TextInput::make('nuri')
                                    ->label('NIP')
                                    ->maxLength(50)
                                    ->required()
                                    ->unique(table: 'students', column: 'nuri', ignoreRecord: true)
                                    ->validationMessages([
                                        'unique' => 'Já existe um agente com este NIP.',
                                    ])
                                    ->default(fn ($record) => $record?->nuri)
                                    ->helperText('Número de Identificação Policial'),

                                Forms\Components\TextInput::make('student_number')
                                    ->label('Nº de Ordem')
                                    ->required()
                                    ->unique(table: 'students', column: 'student_number', ignoreRecord: true)
                                    ->validationMessages([
                                        'unique' => 'Já existe um agente com este Nº de Ordem.',
                                    ])
                                    ->maxLength(191),

                                Forms\Components\Select::make('institution_id')
                                    ->label('Escola de Formação')
                                    ->options(Institution::pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Forms\Components\Select::make('provenance_id')
                                    ->label('Proveniência (Órgão/Unidade)')
                                    ->options(Provenance::pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Forms\Components\Select::make('rank_id')
                                    ->label('Patente')
                                    ->options(Rank::pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Forms\Components\TextInput::make('phone')
                                    ->label('Telefone')
                                    ->tel()
                                    ->prefix('+244')
                                    ->placeholder('9XX XXX XXX')
                                    ->mask('999 999 999')
                                    ->maxLength(20)
                                    ->required()
                                    ->unique(table: 'students', column: 'phone', ignoreRecord: true)
                                    ->validationMessages([
                                        'unique' => 'Já existe um agente com este número de telefone.',
                                    ]),
                                
                                // Campos ocultos necessários
                                Forms\Components\Hidden::make('student_type')
                                    ->default('Formando Superior'),
                                Forms\Components\Hidden::make('status')
                                    ->default('em_formacao'),
                            ])->columns(2),

                        \Filament\Schemas\Components\Tabs\Tab::make('Foto')
                            ->icon('heroicon-o-camera')
                            ->schema([
                                Forms\Components\FileUpload::make('photo')
                                    ->label('Foto do Agente')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                                    ->directory('agents/photos')
                                    ->columnSpanFull(),
                            ]),

                        \Filament\Schemas\Components\Tabs\Tab::make('Documentos')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Forms\Components\FileUpload::make('bilhete_identidade')
                                    ->label('Bilhete de Identidade')
                                    ->directory('agents/documents/bi'),

                                Forms\Components\FileUpload::make('certificado_doc')
                                    ->label('Certificado')
                                    ->directory('agents/documents/certificados'),

                                Forms\Components\FileUpload::make('carta_conducao')
                                    ->label('Carta de Condução')
                                    ->directory('agents/documents/carta'),

                                Forms\Components\FileUpload::make('passaporte')
                                    ->label('Passaporte')
                                    ->directory('agents/documents/passaporte'),
                            ])->columns(2),
                    ])
                    ->columnSpanFull()
                    ->activeTab(1),
            ]);
    }

    public static function table(Table $table): Table
    {
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
                        $name = $record->candidate?->full_name ?? 'Agente';
                        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=0D47A1&color=fff&size=128&bold=true';
                    }),
                Tables\Columns\TextColumn::make('candidate.full_name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('nuri')
                    ->label('NIP')
                    ->searchable(),
                Tables\Columns\TextColumn::make('institution.name')
                    ->label('Escola de Formação')
                    ->sortable()
                    ->wrap()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('student_type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn ($state) => StudentType::where('name', $state)->value('color') ?? 'success'),
                Tables\Columns\TextColumn::make('rank.name')
                    ->label('Patente')
                    ->sortable()
                    ->placeholder('-'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('institution_id')
                    ->label('Escola de Formação')
                    ->relationship('institution', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('provenance_id')
                    ->label('Proveniência')
                    ->relationship('provenance', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->mutateFormDataUsing(function (array $data): array {
                        // DEBUG: Log para verificar os dados recebidos
                        \Illuminate\Support\Facades\Log::info('AgentResource - mutateFormDataUsing', [
                            'full_name_manual' => $data['full_name_manual'] ?? 'VAZIO',
                            'nuri' => $data['nuri'] ?? 'VAZIO',
                            'candidate_id' => $data['candidate_id'] ?? 'VAZIO',
                        ]);
                        
                        // Obter o primeiro tipo de recrutamento disponível
                        $recruitmentTypeId = \App\Models\RecruitmentType::first()?->id;
                        
                        // Nome do agente (prioriza full_name_manual se disponível)
                        $fullName = $data['full_name_manual'] ?? null;
                        $nip = $data['nuri'] ?? $data['nuri_manual'] ?? null;
                        
                        // Se já tem candidate_id válido, usar esse
                        if (!empty($data['candidate_id'])) {
                            // Verificar se o candidato existe
                            $existingCandidate = Candidate::find($data['candidate_id']);
                            if ($existingCandidate) {
                                // Usar o candidato existente
                                unset($data['cadastro_mode']);
                                unset($data['full_name_manual']);
                                unset($data['nuri_manual']);
                                unset($data['candidate_name_display']);
                                return $data;
                            }
                        }
                        
                        // Se não tem candidate_id, precisamos criar um candidato
                        if (empty($data['candidate_id']) && !empty($fullName)) {
                            if ($nip) {
                                // Se tem NIP, buscar ou criar candidato
                                $candidate = Candidate::firstOrCreate(
                                    ['id_number' => $nip],
                                    [
                                        'full_name' => $fullName,
                                        'institution_id' => $data['institution_id'] ?? null,
                                        'provenance_id' => $data['provenance_id'] ?? null,
                                        'current_rank_id' => $data['rank_id'] ?? null,
                                        'recruitment_type_id' => $recruitmentTypeId,
                                        'student_type' => 'Formando Superior', // Marcar como agente
                                        'status' => 'aprovado',
                                    ]
                                );
                            } else {
                                // Se não tem NIP, criar novo candidato
                                $candidate = Candidate::create([
                                    'full_name' => $fullName,
                                    'id_number' => null,
                                    'institution_id' => $data['institution_id'] ?? null,
                                    'provenance_id' => $data['provenance_id'] ?? null,
                                    'current_rank_id' => $data['rank_id'] ?? null,
                                    'recruitment_type_id' => $recruitmentTypeId,
                                    'student_type' => 'Formando Superior', // Marcar como agente
                                    'status' => 'aprovado',
                                ]);
                            }
                            
                            $data['candidate_id'] = $candidate->id;
                            $data['nuri'] = $nip;
                        }
                        
                        // Se ainda não tem candidate_id, exibir erro e parar
                        if (empty($data['candidate_id'])) {
                            \Filament\Notifications\Notification::make()
                                ->title('Erro ao criar agente')
                                ->body('É necessário preencher o nome do agente para criar o cadastro.')
                                ->danger()
                                ->send();
                            throw new \Filament\Support\Exceptions\Halt();
                        }
                        
                        // Remover campos temporários
                        unset($data['cadastro_mode']);
                        unset($data['full_name_manual']);
                        unset($data['nuri_manual']);
                        unset($data['candidate_name_display']);
                        
                        // Garantir valores padrão para campos obrigatórios
                        $data['enrollment_date'] = $data['enrollment_date'] ?? now();
                        
                        return $data;
                    })
                    ->after(function ($record) {
                        // Enviar SMS ao agente após a criação
                        if (!empty($record->phone)) {
                            try {
                                $smsService = app(SmsService::class);
                                
                                // Obter o nome do agente
                                $agentName = $record->candidate?->full_name ?? 'Agente';
                                
                                // Obter o nome da escola
                                $schoolName = $record->institution?->name ?? 'Escola de Formação';
                                
                                // Enviar SMS de notificação
                                $result = $smsService->sendAgentRegistrationNotification(
                                    $record->phone,
                                    $agentName,
                                    $schoolName
                                );
                                
                                if ($result['success']) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('SMS Enviado')
                                        ->body("SMS de notificação enviado para {$record->phone}")
                                        ->success()
                                        ->send();
                                } else {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Falha ao enviar SMS')
                                        ->body('Não foi possível enviar SMS. Verifique a chave API TelcoSMS e o saldo da conta. Detalhes: ' . ($result['message'] ?? 'Erro desconhecido'))
                                        ->warning()
                                        ->duration(8000)
                                        ->send();
                                }
                            } catch (\Exception $e) {
                                \Illuminate\Support\Facades\Log::error('Erro ao enviar SMS para agente', [
                                    'agent_id' => $record->id,
                                    'phone' => $record->phone,
                                    'error' => $e->getMessage(),
                                ]);
                                
                                \Filament\Notifications\Notification::make()
                                    ->title('Erro ao enviar SMS')
                                    ->body('Ocorreu um erro ao tentar enviar o SMS')
                                    ->danger()
                                    ->send();
                            }
                        }
                    })
                    ->modalSubmitAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-check')->label('Criar'))
                    ->modalCancelAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-x-mark')->label('Cancelar')->color('danger'))
                    ->successNotificationTitle('Agente criado com sucesso!')
                    ->label('Novo Agente'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil-square')
                    ->mutateFormDataUsing(function (array $data, $record): array {
                        // Preservar o candidate_id original se não foi alterado
                        if (empty($data['candidate_id']) && $record->candidate_id) {
                            $data['candidate_id'] = $record->candidate_id;
                        }
                        
                        // Se o nome foi alterado, atualizar o candidato
                        $fullName = $data['full_name_manual'] ?? null;
                        if ($fullName && $record->candidate_id) {
                            $candidate = \App\Models\Candidate::find($record->candidate_id);
                            if ($candidate && $candidate->full_name !== $fullName) {
                                $candidate->full_name = $fullName;
                                $candidate->save();
                            }
                        }
                        
                        // Limpar campos temporários
                        unset($data['cadastro_mode']);
                        unset($data['full_name_manual']);
                        unset($data['nuri_manual']);
                        unset($data['candidate_name_display']);
                        
                        // Garantir enrollment_date
                        $data['enrollment_date'] = $data['enrollment_date'] ?? $record->enrollment_date ?? now()->format('Y-m-d');
                        
                        return $data;
                    })
                    ->modalSubmitAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-check')->label('Salvar'))
                    ->modalCancelAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-x-mark')->label('Cancelar')->color('danger'))
                    ->successNotificationTitle('Agente atualizado com sucesso!'),
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
            'index' => Pages\ListAgents::route('/'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('ViewAny:Agent') ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}
