<?php

namespace App\Filament\Escola\Resources\StudentLeaveResource\Pages;

use App\Filament\Escola\Resources\StudentLeaveResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStudentLeave extends EditRecord
{
    protected static string $resource = StudentLeaveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
