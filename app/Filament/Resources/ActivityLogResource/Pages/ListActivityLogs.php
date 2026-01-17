<?php

namespace App\Filament\Resources\ActivityLogResource\Pages;

use App\Filament\Resources\ActivityLogResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\HtmlString;

class ListActivityLogs extends ListRecords
{
    protected static string $resource = ActivityLogResource::class;

    public function getHeading(): string|HtmlString
    {
        return '';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
