<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentLeaveResource\Pages;
use App\Filament\Resources\StudentLeaveResource\RelationManagers;
use App\Models\StudentLeave;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StudentLeaveResource extends Resource
{
    protected static ?string $model = StudentLeave::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-s-clock';
    protected static string|\UnitEnum|null $navigationGroup = 'Gestão Escolar';
    protected static ?string $modelLabel = 'Dispensa/Falta';
    protected static ?string $pluralModelLabel = 'Dispensas';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                \Filament\Schemas\Components\Section::make('Detalhes da Dispensa')
                    ->schema([
                        Forms\Components\Select::make('student_id')
                            ->label('Formando')
                            ->relationship('student', 'student_number')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->student_number} - {$record->candidate->full_name}")
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('leave_type')
                            ->label('Tipo de Dispensa')
                            ->options([
                                'saude' => 'Saúde',
                                'pessoal' => 'Pessoal',
                                'servico' => 'Serviço',
                                'falecimento' => 'Falecimento Familiar',
                                'outro' => 'Outro',
                            ])
                            ->required(),
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Data de Início')
                            ->required(),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Data de Fim')
                            ->required(),
                        Forms\Components\Textarea::make('reason')
                            ->label('Motivo/Justificação')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('approved_by')
                            ->label('Aprovado por')
                            ->relationship('approver', 'full_name') // Assuming Relation is 'approver' to Trainer
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'pending' => 'Pendente',
                                'approved' => 'Aprovada',
                                'rejected' => 'Rejeitada',
                            ])
                            ->required()
                            ->default('pending'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.student_number')
                    ->label('Nº Ordem')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('student.candidate.full_name')
                    ->label('Formando')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('leave_type')
                    ->label('Tipo')
                    ->badge(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Início')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Fim')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registado em')
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
            'index' => Pages\ListStudentLeaves::route('/'),
            'create' => Pages\CreateStudentLeave::route('/create'),
            'edit' => Pages\EditStudentLeave::route('/{record}/edit'),
        ];
    }
}
