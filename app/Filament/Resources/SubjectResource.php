<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubjectResource\Pages;
use App\Filament\Resources\SubjectResource\RelationManagers;
use App\Models\Subject;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-s-bookmark-square';
    protected static string|\UnitEnum|null $navigationGroup = 'Currículo';
    protected static ?string $navigationLabel = 'Disciplinas';
    protected static ?string $modelLabel = 'Disciplina';
    protected static ?string $pluralModelLabel = 'Disciplinas';
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['coursePhase']);
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome da Disciplina')
                    ->required()
                    ->maxLength(191),
                Forms\Components\TextInput::make('workload_hours')
                    ->label('Carga Horária')
                    ->numeric(),
                Forms\Components\Select::make('course_phase_id')
                    ->label('Fase de Curso')
                    ->relationship('coursePhase', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Textarea::make('description')
                    ->label('Descrição')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Disciplina')
                    ->searchable(),
                Tables\Columns\TextColumn::make('workload_hours')
                    ->label('Carga H.')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('coursePhase.name')
                    ->label('Fase')
                    ->sortable(),
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
            'index' => Pages\ListSubjects::route('/'),
            'create' => Pages\CreateSubject::route('/create'),
            'edit' => Pages\EditSubject::route('/{record}/edit'),
        ];
    }
}
