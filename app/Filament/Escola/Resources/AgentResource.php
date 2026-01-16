<?php

namespace App\Filament\Escola\Resources;

use App\Filament\Escola\Resources\AgentResource\Pages;
use App\Models\Student;
use App\Models\Candidate;
use App\Models\Provenance;
use App\Models\Rank;
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
    protected static ?string $navigationLabel = 'Agentes';
    protected static ?string $modelLabel = 'Agente';
    protected static ?string $pluralModelLabel = 'Agentes';
    protected static ?int $navigationSort = 2;

    /**
     * Badge com total de agentes formados da instituição
     */
    public static function getNavigationBadge(): ?string
    {
        $tenant = \Filament\Facades\Filament::getTenant();
        if ($tenant) {
            return (string) Student::where('institution_id', $tenant->id)
                ->whereIn('status', ['em_formacao', 'concluiu'])
                ->where('student_type', 'Formando Superior')
                ->count();
        }
        return null;
    }

    /**
     * Cor do badge
     */
    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    // Filtrar agentes da instituição logada
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->whereIn('status', ['em_formacao', 'concluiu'])
            ->where('student_type', 'Formando Superior')
            ->with(['candidate', 'institution', 'provenance', 'rank']);
        
        // Filtrar pela instituição do tenant
        $tenant = \Filament\Facades\Filament::getTenant();
        if ($tenant) {
            $query->where('institution_id', $tenant->id);
        }
        
        return $query;
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Tabs::make('Cadastro de Agente')
                    ->tabs([
                        \Filament\Schemas\Components\Tabs\Tab::make('Modo de Cadastro')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->hiddenOn('edit')
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
                                Forms\Components\TextInput::make('search_nip')
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
                                    ->visible(fn ($get) => $get('cadastro_mode') === 'automatico' && $get('search_nip'))
                                    ->columnSpanFull(),

                                // Campo oculto para guardar o candidate_id
                                Forms\Components\Hidden::make('candidate_id'),
                            ]),

                        \Filament\Schemas\Components\Tabs\Tab::make('Dados Pessoais')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Forms\Components\TextInput::make('full_name_manual')
                                    ->label('Nome Completo')
                                    ->required()
                                    ->maxLength(191)
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
                                    ->default(fn ($record) => $record?->nuri)
                                    ->helperText('Número de Identificação Policial'),

                                Forms\Components\TextInput::make('student_number')
                                    ->label('Nº de Ordem')
                                    ->required()
                                    ->unique(table: 'students', column: 'student_number', ignoreRecord: true)
                                    ->maxLength(191),

                                // Escola é definida automaticamente
                                Forms\Components\Placeholder::make('institution_info')
                                    ->label('Escola de Formação')
                                    ->content(fn () => \Filament\Facades\Filament::getTenant()?->name ?? 'N/A'),

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
                                    ->required(),
                                
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
                    ->default(fn ($record) => $record->candidate?->full_name ?? 'Sem candidato vinculado')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('candidate', fn ($c) => $c->where('full_name', 'like', "%{$search}%"));
                    })
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('nuri')
                    ->label('NIP')
                    ->searchable(),
                Tables\Columns\TextColumn::make('rank.name')
                    ->label('Patente')
                    ->sortable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('student_type')
                    ->label('Tipo')
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'em_formacao' => 'Em Formação',
                        'concluiu' => 'Concluído',
                        default => $state,
                    })
                    ->color(fn ($state) => match($state) {
                        'em_formacao' => 'warning',
                        'concluiu' => 'success',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'em_formacao' => 'Em Formação',
                        'concluiu' => 'Concluído',
                    ]),
                Tables\Filters\SelectFilter::make('rank_id')
                    ->label('Patente')
                    ->relationship('rank', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->mutateFormDataUsing(function (array $data): array {
                        // Definir automaticamente a instituição do tenant
                        $tenant = \Filament\Facades\Filament::getTenant();
                        if ($tenant) {
                            $data['institution_id'] = $tenant->id;
                        }
                        
                        // Obter tipo de recrutamento
                        $recruitmentTypeId = \App\Models\RecruitmentType::first()?->id;
                        
                        // Nome do agente
                        $fullName = $data['full_name_manual'] ?? null;
                        $nip = $data['nuri'] ?? null;
                        
                        // Se não tem candidate_id, criar candidato
                        if (empty($data['candidate_id']) && !empty($fullName)) {
                            if ($nip) {
                                $candidate = Candidate::firstOrCreate(
                                    ['id_number' => $nip],
                                    [
                                        'full_name' => $fullName,
                                        'institution_id' => $data['institution_id'] ?? null,
                                        'provenance_id' => $data['provenance_id'] ?? null,
                                        'current_rank_id' => $data['rank_id'] ?? null,
                                        'recruitment_type_id' => $recruitmentTypeId,
                                        'student_type' => 'Formando Superior',
                                        'status' => 'aprovado',
                                    ]
                                );
                            } else {
                                $candidate = Candidate::create([
                                    'full_name' => $fullName,
                                    'id_number' => null,
                                    'institution_id' => $data['institution_id'] ?? null,
                                    'provenance_id' => $data['provenance_id'] ?? null,
                                    'current_rank_id' => $data['rank_id'] ?? null,
                                    'recruitment_type_id' => $recruitmentTypeId,
                                    'student_type' => 'Formando Superior',
                                    'status' => 'aprovado',
                                ]);
                            }
                            $data['candidate_id'] = $candidate->id;
                        }
                        
                        // Limpar campos temporários
                        unset($data['cadastro_mode']);
                        unset($data['full_name_manual']);
                        unset($data['search_nip']);
                        unset($data['candidate_name_display']);
                        
                        return $data;
                    })
                    ->after(function (Student $record) {
                        // Enviar SMS ao agente após criação
                        $phone = $record->phone;
                        
                        if (!empty($phone)) {
                            $agentName = $record->candidate?->full_name ?? 'Agente';
                            $institutionName = $record->institution?->name ?? 'Escola de Formacao da Policia Nacional';
                            
                            // Remover acentos
                            $removeAccents = fn($str) => strtr($str, [
                                'ã' => 'a', 'á' => 'a', 'à' => 'a', 'â' => 'a',
                                'é' => 'e', 'ê' => 'e', 'í' => 'i', 'ó' => 'o',
                                'ô' => 'o', 'õ' => 'o', 'ú' => 'u', 'ç' => 'c',
                                'Ã' => 'A', 'Á' => 'A', 'À' => 'A', 'Â' => 'A',
                                'É' => 'E', 'Ê' => 'E', 'Í' => 'I', 'Ó' => 'O',
                                'Ô' => 'O', 'Õ' => 'O', 'Ú' => 'U', 'Ç' => 'C',
                            ]);
                            
                            $institutionName = $removeAccents($institutionName);
                            $agentName = $removeAccents($agentName);
                            
                            try {
                                $smsService = new \App\Services\SmsService();
                                $result = $smsService->sendAgentRegistrationNotification(
                                    $phone,
                                    $agentName,
                                    $institutionName
                                );
                                
                                if ($result['success']) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('SMS Enviado')
                                        ->body("SMS de notificação enviado para {$phone}")
                                        ->success()
                                        ->send();
                                } else {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Falha ao enviar SMS')
                                        ->body('Não foi possível enviar SMS. Detalhes: ' . ($result['message'] ?? 'Erro desconhecido'))
                                        ->warning()
                                        ->send();
                                }
                            } catch (\Exception $e) {
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
                    ->createAnotherAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-plus-circle')->label('Salvar e criar outro'))
                    ->createAnother(true)
                    ->successNotificationTitle('Agente criado com sucesso!')
                    ->label('Novo Agente'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil-square')
                    ->modalSubmitAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-check')->label('Salvar'))
                    ->modalCancelAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-x-mark')->label('Cancelar')->color('danger'))
                    ->successNotificationTitle('Agente atualizado com sucesso!'),
                \Filament\Actions\DeleteAction::make()->icon('heroicon-o-trash'),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAgents::route('/'),
        ];
    }
}
