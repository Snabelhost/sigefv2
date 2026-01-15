<?php

namespace App\Filament\Escola\Resources\PautaResource\Pages;

use App\Filament\Escola\Resources\PautaResource;
use Filament\Resources\Pages\ListRecords;

class ListPautas extends ListRecords
{
    protected static string $resource = PautaResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
