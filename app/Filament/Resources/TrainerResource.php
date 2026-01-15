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
                \Filament\Schemas\Components\Section::make('Tipo de Formador')
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
                            ->columnSpan(2),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Identificação')
                    ->schema([
                        Forms\Components\FileUpload::make('photo')
                            ->label('Foto')
                            ->image()
                            ->avatar()
                            ->directory('trainers'),
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

                // Campos para Fardado
                \Filament\Schemas\Components\Section::make('Dados do Fardado')
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
                        Forms\Components\TextInput::make('organ')
                            ->label('Órgão/Unidade')
                            ->maxLength(191),
                    ])->columns(3)
                    ->visible(fn ($get): bool => $get('trainer_type') === 'Fardado'),

                // Campos para Civil
                \Filament\Schemas\Components\Section::make('Dados do Civil')
                    ->schema([
                        Forms\Components\TextInput::make('bilhete')
                            ->label('Bilhete de Identidade')
                            ->maxLength(191),
                    ])->columns(1)
                    ->visible(fn ($get): bool => $get('trainer_type') === 'Civil'),

                \Filament\Schemas\Components\Section::make('Informação Adicional')
                    ->schema([
                        Forms\Components\TextInput::make('education_level')
                            ->label('Nível Académico')
                            ->maxLength(191),
                        Forms\Components\TextInput::make('phone')
                            ->label('Telefone')
                            ->tel()
                            ->maxLength(191),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true)
                            ->required(),
                    ])->columns(3),
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
