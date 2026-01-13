<?php

namespace App\Filament\Resources\SelectionTestResource\Pages;

use App\Filament\Resources\SelectionTestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSelectionTest extends EditRecord
{
    protected static string $resource = SelectionTestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
