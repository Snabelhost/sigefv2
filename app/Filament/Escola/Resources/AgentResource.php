<?php

namespace App\Filament\Escola\Resources;

use App\Filament\Escola\Resources\AgentResource\Pages;
use App\Models\Student;
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
    protected static ?int $navigationSort = 1;

    // Filtrar apenas formandos com status "concluiu"
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status', 'concluiu')
            ->with(['candidate', 'currentPhase']);
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Section::make('Informação do Agente')
                    ->schema([
                        Forms\Components\Select::make('candidate_id')
                            ->label('Candidato')
                            ->relationship('candidate', 'full_name')
                            ->required()
                            ->searchable()
                            ->disabled(),
                        Forms\Components\TextInput::make('student_number')
                            ->label('Nº de Ordem')
                            ->required()
                            ->disabled(),
                        Forms\Components\TextInput::make('nuri')
                            ->label('NURI')
                            ->maxLength(191),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Dados Profissionais')
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
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('student_number')
                    ->label('Nº Ordem')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('candidate.full_name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nuri')
                    ->label('NURI')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cia')
                    ->label('CIA'),
                Tables\Columns\TextColumn::make('enrollment_date')
                    ->label('Data Conclusão')
                    ->date()
                    ->sortable(),
            ])

            ->filters([])
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
    \Filament\Actions\EditAction::make()->icon('heroicon-o-pencil-square'),
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
