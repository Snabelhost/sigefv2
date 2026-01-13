<?php

namespace App\Filament\Dpq\Resources;

use App\Filament\Dpq\Resources\CandidateResource\Pages;
use App\Models\Candidate;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CandidateResource extends Resource
{
    protected static ?string $model = Candidate::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-s-identification';
    protected static ?string $modelLabel = 'Candidato';
    protected static ?string $pluralModelLabel = 'Candidatos';
    
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['recruitmentType', 'academicYear']);
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
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Processo de Recrutamento')
                    ->schema([
                        Forms\Components\Select::make('recruitment_type_id')
                            ->label('Tipo de Recrutamento')
                            ->relationship('recruitmentType', 'name')
                            ->required()
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
                        Forms\Components\Select::make('academic_year_id')
                            ->label('Ano Académico')
                            ->relationship('academicYear', 'year')
                            ->required()
                            ->preload(),
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
                    ->label('Nome Completo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('id_number')
                    ->label('Nº BI')
                    ->searchable(),
                Tables\Columns\TextColumn::make('recruitmentType.name')
                    ->label('Recrutamento')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'info' => 'admitted',
                    ]),
            ])
            ->filters([
                //
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
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
