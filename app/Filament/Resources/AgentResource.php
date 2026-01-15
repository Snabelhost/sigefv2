<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AgentResource\Pages;
use App\Models\Student;
use App\Models\Candidate;
use App\Models\Institution;
use App\Models\Provenance;
use App\Models\Rank;
use App\Models\StudentType;
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
     * Badge com total de agentes
     */
    public static function getNavigationBadge(): ?string
    {
        return (string) Student::whereIn('status', ['em_formacao', 'concluiu'])->count();
    }

    /**
     * Cor do badge
     */
    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    // Filtrar agentes (Em Formação ou Formação Concluída)
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('status', ['em_formacao', 'concluiu'])
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
                                    ->default(fn ($record) => $record?->candidate?->full_name)
                                    ->afterStateHydrated(function ($state, $set, $record) {
                                        if ($record && !$state) {
                                            $set('full_name_manual', $record->candidate?->full_name);
                                        }
                                    })
                                    ->helperText(fn ($get) => $get('cadastro_mode') === 'automatico' 
                                        ? 'Preenchido automaticamente pelo NIP' 
                                        : 'Digite o nome completo do agente')
                                    ->columnSpanFull(),

                                // NIP - visível apenas no modo manual ou na edição
                                Forms\Components\TextInput::make('nuri_manual')
                                    ->label('NIP (Número de Identificação Policial)')
                                    ->visible(fn ($get, $operation) => $get('cadastro_mode') === 'manual' || $operation === 'edit')
                                    ->default(fn ($record) => $record?->nuri)
                                    ->afterStateHydrated(function ($state, $set, $record) {
                                        if ($record && !$state) {
                                            $set('nuri_manual', $record->nuri);
                                        }
                                    })
                                    ->maxLength(191),

                                Forms\Components\TextInput::make('student_number')
                                    ->label('Nº de Ordem')
                                    ->required()
                                    ->unique(table: 'students', column: 'student_number', ignoreRecord: true)
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
                                    ->preload(),

                                Forms\Components\Select::make('rank_id')
                                    ->label('Patente')
                                    ->options(Rank::pluck('name', 'id'))
                                    ->searchable()
                                    ->preload(),
                            ])->columns(2),

                        \Filament\Schemas\Components\Tabs\Tab::make('Informação Militar')
                            ->icon('heroicon-o-building-office')
                            ->schema([
                                Forms\Components\Select::make('student_type')
                                    ->label('Tipo de Aluno')
                                    ->options(fn () => StudentType::where('is_active', true)->orderBy('order')->pluck('name', 'name')->toArray())
                                    ->default(fn () => StudentType::where('is_active', true)->orderBy('order', 'desc')->value('name') ?? 'Formando Superior')
                                    ->required(),

                                Forms\Components\Select::make('status')
                                    ->label('Estado')
                                    ->options([
                                        'em_formacao' => 'Em Formação',
                                        'concluiu' => 'Formação Concluída',
                                    ])
                                    ->default('em_formacao')
                                    ->required(),

                                Forms\Components\TextInput::make('cia')
                                    ->label('Companhia')
                                    ->maxLength(191),

                                Forms\Components\TextInput::make('platoon')
                                    ->label('Pelotão')
                                    ->maxLength(191),

                                Forms\Components\TextInput::make('section')
                                    ->label('Secção')
                                    ->maxLength(191),

                                Forms\Components\DatePicker::make('enrollment_date')
                                    ->label('Data de Matrícula')
                                    ->default(now()),

                                Forms\Components\DatePicker::make('conclusion_date')
                                    ->label('Data de Conclusão'),
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
                    ->persistTabInQueryString(),
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
                Tables\Columns\TextColumn::make('conclusion_date')
                    ->label('Data de Conclusão')
                    ->date('d/m/Y')
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
                        // Se modo manual, criar candidato primeiro
                        if (isset($data['cadastro_mode']) && $data['cadastro_mode'] === 'manual' && !empty($data['full_name_manual'])) {
                            // Obter o primeiro tipo de recrutamento disponível
                            $recruitmentTypeId = \App\Models\RecruitmentType::first()?->id;
                            
                            // Verificar se já existe candidato com este NIP
                            $nip = $data['nuri_manual'] ?? null;
                            
                            if ($nip) {
                                // Se tem NIP, buscar ou criar candidato
                                $candidate = Candidate::firstOrCreate(
                                    ['id_number' => $nip],
                                    [
                                        'full_name' => $data['full_name_manual'],
                                        'institution_id' => $data['institution_id'] ?? null,
                                        'provenance_id' => $data['provenance_id'] ?? null,
                                        'current_rank_id' => $data['rank_id'] ?? null,
                                        'recruitment_type_id' => $recruitmentTypeId,
                                        'status' => 'aprovado',
                                    ]
                                );
                            } else {
                                // Se não tem NIP, criar novo candidato
                                $candidate = Candidate::create([
                                    'full_name' => $data['full_name_manual'],
                                    'id_number' => null,
                                    'institution_id' => $data['institution_id'] ?? null,
                                    'provenance_id' => $data['provenance_id'] ?? null,
                                    'current_rank_id' => $data['rank_id'] ?? null,
                                    'recruitment_type_id' => $recruitmentTypeId,
                                    'status' => 'aprovado',
                                ]);
                            }
                            
                            $data['candidate_id'] = $candidate->id;
                            $data['nuri'] = $nip;
                        }
                        
                        // Remover campos temporários
                        unset($data['cadastro_mode']);
                        unset($data['full_name_manual']);
                        unset($data['nuri_manual']);
                        unset($data['candidate_name_display']);
                        
                        return $data;
                    })
                    ->modalSubmitAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-check')->label('Criar'))
                    ->modalCancelAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-x-mark')->label('Cancelar')->color('danger'))
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
}
