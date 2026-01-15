<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrainerResource\Pages;
use App\Filament\Resources\TrainerResource\RelationManagers;
use App\Models\Trainer;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Closure;

class TrainerResource extends Resource
{
    protected static ?string $model = Trainer::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-s-presentation-chart-bar';
    protected static string|\UnitEnum|null $navigationGroup = 'Gestão Escolar';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Formador';
    protected static ?string $pluralModelLabel = 'Formadores';
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['rank', 'institution']);
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Wizard::make([
                    \Filament\Schemas\Components\Wizard\Step::make('Tipo')
                        ->description('Selecione o tipo de formador')
                        ->icon('heroicon-o-user-group')
                        ->schema([
                            Forms\Components\Select::make('trainer_type')
                                ->label('Tipo de Formador')
                                ->options([
                                    'Fardado' => 'Fardado',
                                    'Civil' => 'Civil',
                                ])
                                ->default('Fardado')
                                ->required()
                                ->live()
                                ->columnSpanFull(),
                        ]),

                    \Filament\Schemas\Components\Wizard\Step::make('Identificação')
                        ->description('Dados pessoais do formador')
                        ->icon('heroicon-o-identification')
                        ->schema([
                            Forms\Components\FileUpload::make('photo')
                                ->label('Foto')
                                ->image()
                                ->avatar()
                                ->directory('trainers')
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('full_name')
                                ->label('Nome Completo')
                                ->required()
                                ->maxLength(191),
                            Forms\Components\Select::make('gender')
                                ->label('Género')
                                ->options([
                                    'Masculino' => 'Masculino',
                                    'Feminino' => 'Feminino',
                                ])
                                ->required(),
                            Forms\Components\Select::make('institution_id')
                                ->label('Instituição (Escola)')
                                ->relationship('institution', 'name')
                                ->required()
                                ->searchable()
                                ->preload(),
                        ])->columns(2),

                    \Filament\Schemas\Components\Wizard\Step::make('Dados Profissionais')
                        ->description('Informações profissionais')
                        ->icon('heroicon-o-briefcase')
                        ->schema([
                            // Campos para Fardado
                            \Filament\Schemas\Components\Fieldset::make('Dados do Fardado')
                                ->schema([
                                    Forms\Components\TextInput::make('nip')
                                        ->label('NIP')
                                        ->unique(ignoreRecord: true)
                                        ->maxLength(191),
                                    Forms\Components\Select::make('rank_id')
                                        ->label('Patente')
                                        ->relationship('rank', 'name')
                                        ->searchable()
                                        ->preload(),
                                    Forms\Components\Select::make('organ')
                                        ->label('Órgão/Unidade')
                                        ->options([
                                            'Comando Geral' => 'Comando Geral',
                                            'Direcção de Pessoal e Quadros' => 'Direcção de Pessoal e Quadros',
                                            'Direcção de Ordem Pública' => 'Direcção de Ordem Pública',
                                            'Direcção de Investigação Criminal' => 'Direcção de Investigação Criminal',
                                            'Direcção de Trânsito' => 'Direcção de Trânsito',
                                            'Direcção de Protecção Pública' => 'Direcção de Protecção Pública',
                                            'Direcção de Fronteiras' => 'Direcção de Fronteiras',
                                            'Direcção de Logística' => 'Direcção de Logística',
                                            'Direcção de Finanças' => 'Direcção de Finanças',
                                            'Direcção de Saúde' => 'Direcção de Saúde',
                                            'Direcção de Instrução e Ensino' => 'Direcção de Instrução e Ensino',
                                            'Direcção de Comunicações' => 'Direcção de Comunicações',
                                            'Gabinete de Estudos e Planeamento' => 'Gabinete de Estudos e Planeamento',
                                            'Gabinete Jurídico' => 'Gabinete Jurídico',
                                            'Gabinete de Intercâmbio' => 'Gabinete de Intercâmbio',
                                            'Comando Provincial de Luanda' => 'Comando Provincial de Luanda',
                                            'Comando Provincial de Benguela' => 'Comando Provincial de Benguela',
                                            'Comando Provincial do Huambo' => 'Comando Provincial do Huambo',
                                            'Comando Provincial de Cabinda' => 'Comando Provincial de Cabinda',
                                            'Comando Provincial do Uíge' => 'Comando Provincial do Uíge',
                                            'Comando Provincial do Zaire' => 'Comando Provincial do Zaire',
                                            'Comando Provincial de Malanje' => 'Comando Provincial de Malanje',
                                            'Comando Provincial da Lunda Norte' => 'Comando Provincial da Lunda Norte',
                                            'Comando Provincial da Lunda Sul' => 'Comando Provincial da Lunda Sul',
                                            'Comando Provincial do Moxico' => 'Comando Provincial do Moxico',
                                            'Comando Provincial do Cuando Cubango' => 'Comando Provincial do Cuando Cubango',
                                            'Comando Provincial da Huíla' => 'Comando Provincial da Huíla',
                                            'Comando Provincial do Namibe' => 'Comando Provincial do Namibe',
                                            'Comando Provincial do Cunene' => 'Comando Provincial do Cunene',
                                            'Comando Provincial do Bié' => 'Comando Provincial do Bié',
                                            'Comando Provincial do Cuanza Norte' => 'Comando Provincial do Cuanza Norte',
                                            'Comando Provincial do Cuanza Sul' => 'Comando Provincial do Cuanza Sul',
                                            'Comando Provincial do Bengo' => 'Comando Provincial do Bengo',
                                        ])
                                        ->searchable()
                                        ->preload(),
                                ])->columns(3)
                                ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get): bool => $get('trainer_type') === 'Fardado'),

                            // Campos para Civil
                            \Filament\Schemas\Components\Fieldset::make('Dados do Civil')
                                ->schema([
                                    Forms\Components\TextInput::make('bilhete')
                                        ->label('Bilhete de Identidade')
                                        ->maxLength(191)
                                        ->columnSpanFull(),
                                ])->columns(1)
                                ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get): bool => $get('trainer_type') === 'Civil'),
                        ]),

                    \Filament\Schemas\Components\Wizard\Step::make('Finalização')
                        ->description('Informações adicionais')
                        ->icon('heroicon-o-check-circle')
                        ->schema([
                            Forms\Components\Select::make('education_level')
                                ->label('Nível Académico')
                                ->options([
                                    'Ensino Primário' => 'Ensino Primário',
                                    '7ª Classe' => '7ª Classe',
                                    '8ª Classe' => '8ª Classe',
                                    '9ª Classe' => '9ª Classe',
                                    '10ª Classe' => '10ª Classe',
                                    '11ª Classe' => '11ª Classe',
                                    '12ª Classe' => '12ª Classe',
                                    'Ensino Médio Técnico' => 'Ensino Médio Técnico',
                                    'Bacharelato' => 'Bacharelato',
                                    'Licenciatura' => 'Licenciatura',
                                    'Pós-Graduação' => 'Pós-Graduação',
                                    'Mestrado' => 'Mestrado',
                                    'Doutoramento' => 'Doutoramento',
                                ])
                                ->searchable()
                                ->preload(),
                            Forms\Components\TextInput::make('phone')
                                ->label('Telefone')
                                ->tel()
                                ->maxLength(191),
                            Forms\Components\Toggle::make('is_active')
                                ->label('Activo')
                                ->default(true)
                                ->required(),
                        ])->columns(3),
                ])->columnSpanFull(),
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
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->full_name ?? 'F') . '&background=0D47A1&color=fff&size=128'),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('bilhete')
                    ->label('Bilhete')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('rank.name')
                    ->label('Patente')
                    ->sortable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('institution.name')
                    ->label('Escola')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('trainer_type')
                    ->label('Tipo')
                    ->badge()
                    ->colors([
                        'primary' => 'Fardado',
                        'success' => 'Civil',
                    ]),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('trainer_type')
                    ->label('Tipo')
                    ->options([
                        'Fardado' => 'Fardado',
                        'Civil' => 'Civil',
                    ]),
            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->modalSubmitAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-check')->label('Criar'))
                    ->modalCancelAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-x-mark')->label('Cancelar')->color('danger'))
                    ->createAnotherAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-plus-circle')->label('Salvar e criar outro'))
                    ->createAnother(true)
                    ->successNotificationTitle('Registo criado com sucesso!')
                    ->label('Novo Formador'),
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
            'index' => Pages\ListTrainers::route('/'),
        ];
    }
}
