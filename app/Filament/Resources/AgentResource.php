<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AgentResource\Pages;
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
    protected static string|\UnitEnum|null $navigationGroup = 'Gestão Escolar';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Agentes';
    protected static ?string $modelLabel = 'Agente';
    protected static ?string $pluralModelLabel = 'Agentes';

    // Filtrar apenas formandos com status "concluiu"
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('status', 'concluiu')
            ->with(['candidate', 'institution', 'currentPhase']);
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
                        Forms\Components\Select::make('institution_id')
                            ->label('Escola de Formação')
                            ->relationship('institution', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
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
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('student_number')
                    ->label('Nº Ordem')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('candidate.full_name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('institution.name')
                    ->label('Escola de Formação')
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
            ->filters([
                //
            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->modalSubmitAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-check')->label('Criar'))
                    ->modalCancelAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-x-mark')->label('Cancelar')->color('danger'))
                    ->successNotificationTitle('Registo criado com sucesso!')
                    ->label('Novo Agente'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make()
                    ->icon('heroicon-o-pencil-square')
                    ->modalSubmitAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-check')->label('Salvar'))
                    ->modalCancelAction(fn (\Filament\Actions\Action $action) => $action->icon('heroicon-o-x-mark')->label('Cancelar')->color('danger'))
                    ->successNotificationTitle('Registo atualizado com sucesso!'),
                \Filament\Actions\Action::make('certificado')
                    ->label('Certificado')
                    ->icon('heroicon-s-document-check')
                    ->color('success')
                    ->url(fn ($record) => route('reports.certificate', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                //
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
