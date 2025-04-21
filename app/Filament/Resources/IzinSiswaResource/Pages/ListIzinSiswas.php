<?php

namespace App\Filament\Resources\IzinSiswaResource\Pages;

use App\Filament\Resources\IzinSiswaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIzinSiswas extends ListRecords
{
    protected static string $resource = IzinSiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
