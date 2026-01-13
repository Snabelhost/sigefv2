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
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['course', 'institution', 'academicYear']);
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
                Forms\Components\Select::make('academic_year_id')
                    ->label('Ano Académico')
                    ->relationship('academicYear', 'year')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('organ')
                    ->label('Órgão')
                    ->maxLength(191),
                Forms\Components\TextInput::make('max_students')
                    ->label('Capacidade/Vagas')
                    ->required()
                    ->numeric()
                    ->default(0),
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
            ->columns([
                Tables\Columns\TextColumn::make('course.name')
                    ->label('Curso')
                    ->sortable(),
                Tables\Columns\TextColumn::make('institution.acronym')
                    ->label('Instituição')
                    ->sortable(),
                Tables\Columns\TextColumn::make('academicYear.year')
                    ->label('Ano')
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
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
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
                \Filament\Actions\EditAction::make()->icon('heroicon-o-pencil-square'),
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
}



