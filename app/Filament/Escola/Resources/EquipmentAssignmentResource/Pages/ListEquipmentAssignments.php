<?php

namespace App\Filament\Escola\Resources\EquipmentAssignmentResource\Pages;

use App\Filament\Escola\Resources\EquipmentAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEquipmentAssignments extends ListRecords
{
    protected static string $resource = EquipmentAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
