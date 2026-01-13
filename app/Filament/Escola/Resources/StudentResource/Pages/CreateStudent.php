<?php

namespace App\Filament\Escola\Resources\StudentResource\Pages;

use App\Filament\Escola\Resources\StudentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;
}
