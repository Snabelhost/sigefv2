<?php

namespace App\Filament\Resources\SelectionTestResource\Pages;

use App\Filament\Resources\SelectionTestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSelectionTests extends ListRecords
{
    protected static string $resource = SelectionTestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
