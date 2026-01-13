<?php

namespace App\Filament\Escola\Resources\StudentClassResource\Pages;

use App\Filament\Escola\Resources\StudentClassResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStudentClass extends EditRecord
{
    protected static string $resource = StudentClassResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
