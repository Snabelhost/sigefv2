<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CandidateResource\Pages;
use App\Filament\Resources\CandidateResource\RelationManagers;
use App\Models\Candidate;
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
     * Badge com total de alistados
     */
    public static function getNavigationBadge(): ?string
    {
        return (string) \App\Models\Candidate::count();
    }

    /**
     * Cor do badge
     */
    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    // Eager loading para evitar problema N+1
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['recruitmentType', 'academicYear', 'provenance']);
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Section::make('Identificação Pessoal')
                    ->schema([
                        Forms\Components\FileUpload::make('photo')
                            ->label('Foto')
                            ->image()
                            ->avatar()
                            ->directory('candidates'),
                        Forms\Components\TextInput::make('full_name')
                            ->label('Nome Completo')
                            ->required()
                            ->maxLength(191),
                        Forms\Components\TextInput::make('id_number')
                            ->label('Nº do BI')
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->maxLength(191),
                        Forms\Components\DatePicker::make('birth_date')
                            ->label('Data de Nascimento')
                            ->required(),
                        Forms\Components\Select::make('gender')
                            ->label('Género')
                            ->options([
                                'M' => 'Masculino',
                                'F' => 'Feminino',
                            ])
                            ->required(),
                        Forms\Components\Select::make('marital_status')
                            ->label('Estado Civil')
                            ->options([
                                'solteiro' => 'Solteiro(a)',
                                'casado' => 'Casado(a)',
                                'divorciado' => 'Divorciado(a)',
                                'viuvo' => 'Viúvo(a)',
                            ]),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Habilitações e Proveniência')
                    ->schema([
                        Forms\Components\TextInput::make('education_level')
                            ->label('Nível Académico')
                            ->placeholder('Ex: 12ª Classe, Licenciatura')
                            ->maxLength(191),
                        Forms\Components\TextInput::make('education_area')
                            ->label('Área de Formação')
                            ->maxLength(191),
                        Forms\Components\Select::make('provenance_id')
                            ->label('Proveniência')
                            ->relationship('provenance', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('current_rank_id')
                            ->label('Patente Actual')
                            ->relationship('currentRank', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\DatePicker::make('pna_entry_date')
                            ->label('Data de Ingresso na PNA'),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Contacto e Processo')
                    ->schema([
                        Forms\Components\TextInput::make('phone')
                            ->label('Telefone')
                            ->tel()
                            ->maxLength(191),
                        Forms\Components\TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->maxLength(191),
                        Forms\Components\TextInput::make('father_name')
                            ->label('Nome do Pai')
                            ->maxLength(191),
                        Forms\Components\TextInput::make('mother_name')
                            ->label('Nome da Mãe')
                            ->maxLength(191),
                        Forms\Components\Select::make('recruitment_type_id')
                            ->label('Tipo de Recrutamento')
                            ->relationship('recruitmentType', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('academic_year_id')
                            ->label('Ano Académico')
                            ->relationship('academicYear', 'year')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('status')
                            ->label('Estado do Processo')
                            ->options([
                                'pending' => 'Pendente',
                                'approved' => 'Aprovado',
                                'rejected' => 'Rejeitado',
                                'admitted' => 'Admitido (Formando)',
                            ])
                            ->required()
                            ->default('pending'),
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
                    ->circular(),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nome Completo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('id_number')
                    ->label('Nº BI')
                    ->searchable(),
                Tables\Columns\TextColumn::make('recruitmentType.name')
                    ->label('Recrutamento')
                    ->sortable(),
                Tables\Columns\TextColumn::make('gender')
                    ->label('Género'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'info' => 'admitted',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data Registo')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->modalSubmitAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-check')->label('Criar'))
                    ->modalCancelAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-x-mark')->label('Cancelar')->color('danger'))
                    ->createAnotherAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-plus-circle')->label('Salvar e criar outro'))
                    ->createAnother(true)
                    ->successNotificationTitle('Registo criado com sucesso!'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil-square')
                    ->modalSubmitAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-check')->label('Salvar'))
                    ->modalCancelAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-x-mark')->label('Cancelar')->color('danger'))
                    ->successNotificationTitle('Registo atualizado com sucesso!'),
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
}
