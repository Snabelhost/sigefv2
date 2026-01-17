<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseMapResource\Pages;
use App\Filament\Resources\CourseMapResource\RelationManagers;
use App\Models\CourseMap;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CourseMapResource extends Resource
{
    protected static ?string $model = CourseMap::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-s-puzzle-piece';
    protected static ?string $navigationLabel = 'Mapas de Curso';

    public static function getModelLabel(): string
    {
        return 'Mapa de Curso';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Mapas de Curso';
    }

    protected static string|\UnitEnum|null $navigationGroup = 'Currículo';
    protected static ?int $navigationSort = 1;
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['course', 'institution']);
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\Select::make('course_id')
                    ->label('Curso')
                    ->relationship('course', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('institution_id')
                    ->label('Instituição')
                    ->relationship('institution', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('organ')
                    ->label('Órgão')
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
                Forms\Components\TextInput::make('max_students')
                    ->label('Capacidade/Vagas')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\DatePicker::make('start_date')
                    ->label('Data de Início')
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y'),
                Forms\Components\DatePicker::make('end_date')
                    ->label('Data do Fim')
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->afterOrEqual('start_date'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->striped()
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('course.name')
                    ->label('Curso')
                    ->sortable(),
                Tables\Columns\TextColumn::make('institution.acronym')
                    ->label('Instituição')
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Início')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Fim')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_students')
                    ->label('Vagas')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('organ')
                    ->label('Órgão')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
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
            'index' => Pages\ListCourseMaps::route('/'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('ViewAny:CourseMap') ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}
