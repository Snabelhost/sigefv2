<?php

namespace App\Filament\Escola\Resources;

use App\Filament\Escola\Resources\StudentClassResource\Pages;
use App\Models\StudentClass;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StudentClassResource extends Resource
{
    protected static ?string $model = StudentClass::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-s-users';
    protected static ?string $modelLabel = 'Turma';
    protected static ?string $pluralModelLabel = 'Turmas';
    
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['courseMap', 'academicYear']);
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Section::make('Detalhes da Turma')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome da Turma')
                            ->required()
                            ->maxLength(191),
                        Forms\Components\Select::make('course_map_id')
                            ->label('Plano de Curso')
                            ->relationship('courseMap', 'id')
                            ->required()
                            ->preload(),
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('courseMap.id')
                    ->label('Plano ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('academicYear.year')
                    ->label('Ano')
                    ->sortable(),
            ])

            ->filters([
                //
            ])
            ->actions([
                \Filament\Actions\EditAction::make()->icon('heroicon-o-pencil-square'),
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
            'index' => Pages\ListStudentClasses::route('/'),
        ];
    }
}
