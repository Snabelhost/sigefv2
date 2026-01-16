<?php

namespace App\Filament\Escola\Resources;

use App\Filament\Escola\Resources\AgentResource\Pages;
use App\Models\Student;
use App\Models\Institution;
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
     * Badge com total de agentes formados
     */
    public static function getNavigationBadge(): ?string
    {
        return (string) Student::where('status', 'concluiu')->count();
    }

    /**
     * Cor do badge
     */
    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    // Filtrar apenas formandos com status "concluiu" DA INSTITUIÇÃO LOGADA
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->where('status', 'concluiu')
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
                \Filament\Schemas\Components\Section::make('Dados Pessoais')
                    ->schema([
                        Forms\Components\TextInput::make('candidate.full_name')
                            ->label('Nome')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn ($record) => $record?->candidate?->full_name),
                        Forms\Components\TextInput::make('nuri')
                            ->label('NIP')
                            ->maxLength(191),
                        Forms\Components\TextInput::make('student_number')
                            ->label('Nº de Ordem')
                            ->required()
                            ->maxLength(191),
                    ])->columns(3),

                \Filament\Schemas\Components\Section::make('Informação Profissional')
                    ->schema([
                        // Instituição é definida automaticamente pelo tenant - não precisa selecionar
                        Forms\Components\Placeholder::make('institution_info')
                            ->label('Escola de Formação')
                            ->content(fn () => \Filament\Facades\Filament::getTenant()?->name ?? 'N/A'),
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
                        Forms\Components\Select::make('student_type')
                            ->label('Tipo de Aluno')
                            ->options([
                                'Agente' => 'Agente',
                            ])
                            ->default('Agente')
                            ->disabled()
                            ->dehydrated(true),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Unidade')
                    ->schema([
                        Forms\Components\TextInput::make('cia')
                            ->label('Companhia')
                            ->maxLength(191),
                        Forms\Components\TextInput::make('platoon')
                            ->label('Pelotão')
                            ->maxLength(191),
                        Forms\Components\TextInput::make('section')
                            ->label('Secção')
                            ->maxLength(191),
                        Forms\Components\DatePicker::make('conclusion_date')
                            ->label('Data de Conclusão'),
                    ])->columns(4),

                \Filament\Schemas\Components\Section::make('Documentos')
                    ->schema([
                        Forms\Components\FileUpload::make('photo')
                            ->label('Foto do Agente')
                            ->image()
                            ->imageEditor()
                            ->directory('agents/photos')
                            ->visibility('public')
                            ->columnSpan(2),
                        Forms\Components\FileUpload::make('bilhete_identidade')
                            ->label('Bilhete de Identidade')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->directory('agents/documents/bi')
                            ->visibility('public'),
                        Forms\Components\FileUpload::make('certificado_doc')
                            ->label('Certificado')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->directory('agents/documents/certificados')
                            ->visibility('public'),
                        Forms\Components\FileUpload::make('carta_conducao')
                            ->label('Carta de Condução')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->directory('agents/documents/carta')
                            ->visibility('public'),
                        Forms\Components\FileUpload::make('passaporte')
                            ->label('Passaporte')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->directory('agents/documents/passaporte')
                            ->visibility('public'),
                    ])->columns(2),
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
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->candidate->full_name ?? 'A') . '&background=0D47A1&color=fff&size=128'),
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
                    ->color('success')
                    ->default('Agente'),
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
                        return $data;
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
