<?php

namespace App\Filament\Resources\CoursePhaseResource\Pages;

use App\Filament\Resources\CoursePhaseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCoursePhase extends EditRecord
{
    protected static string $resource = CoursePhaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
