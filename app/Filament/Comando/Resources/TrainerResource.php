<?php

namespace App\Filament\Comando\Resources;

use App\Filament\Comando\Resources\TrainerResource\Pages;
use App\Models\Trainer;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TrainerResource extends Resource
{
    protected static ?string $model = Trainer::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-s-user-group';
    protected static ?string $modelLabel = 'Formador';
    protected static ?string $pluralModelLabel = 'Formadores';
    
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['rank']);
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
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
                        Forms\Components\TextInput::make('nip')
                            ->label('NIP')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(191),
                        Forms\Components\Select::make('gender')
                            ->label('Género')
                            ->options([
                                'M' => 'Masculino',
                                'F' => 'Feminino',
                            ])
                            ->required(),
                        Forms\Components\Select::make('rank_id')
                            ->label('Patente')
                            ->relationship('rank', 'name')
                            ->searchable()
                            ->preload(),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Informação Profissional')
                    ->schema([
                        Forms\Components\TextInput::make('organ')
                            ->label('Órgão/Unidade')
                            ->maxLength(191),
                        Forms\Components\TextInput::make('education_level')
                            ->label('Nível Académico')
                            ->maxLength(191),
                        Forms\Components\TextInput::make('phone')
                            ->label('Telefone')
                            ->tel()
                            ->maxLength(191),
                        Forms\Components\Select::make('trainer_type')
                            ->label('Tipo de Formador')
                            ->options([
                                'interno' => 'Interno',
                                'externo' => 'Externo',
                            ])
                            ->required(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true)
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->striped()
            ->columns([
                Tables\Columns\ImageColumn::make('photo')
                    ->label('Foto')
                    ->circular(),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable(),
                Tables\Columns\TextColumn::make('rank.name')
                    ->label('Patente')
                    ->sortable(),
                Tables\Columns\TextColumn::make('trainer_type')
                    ->label('Tipo')
                    ->colors([
                        'primary' => 'interno',
                        'success' => 'externo',
                    ]),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                \Filament\Actions\EditAction::make()
                    ->label('Editar'),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make()
                        ->label('Eliminar'),
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
