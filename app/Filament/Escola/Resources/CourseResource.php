<?php

namespace App\Filament\Escola\Resources;

use App\Filament\Escola\Resources\CourseResource\Pages;
use App\Models\Course;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-s-academic-cap';
    protected static ?string $navigationLabel = 'Cursos';
    protected static ?string $modelLabel = 'Curso';
    protected static ?string $pluralModelLabel = 'Cursos';
    protected static ?int $navigationSort = 0;
    protected static string|\UnitEnum|null $navigationGroup = 'Currículo';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Section::make('Informação do Curso')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome do Curso')
                            ->required()
                            ->maxLength(191),
                        Forms\Components\Textarea::make('description')
                            ->label('Descrição')
                            ->rows(3),
                        Forms\Components\TextInput::make('duration_months')
                            ->label('Duração (meses)')
                            ->numeric()
                            ->suffix('meses'),
                        Forms\Components\Toggle::make('has_phases')
                            ->label('Possui Fases')
                            ->default(false),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration_months')
                    ->label('Duração')
                    ->suffix(' meses'),
                Tables\Columns\IconColumn::make('has_phases')
                    ->label('Fases')
                    ->boolean(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Descrição')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([])
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
        ];
    }
}
