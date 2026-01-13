<?php

namespace App\Filament\Resources\CoursePhaseResource\Pages;

use App\Filament\Resources\CoursePhaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCoursePhases extends ListRecords
{
    protected static string $resource = CoursePhaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

