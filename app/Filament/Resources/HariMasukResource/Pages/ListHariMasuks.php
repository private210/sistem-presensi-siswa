<?php

namespace App\Filament\Resources\HariMasukResource\Pages;

use App\Filament\Resources\HariMasukResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHariMasuks extends ListRecords
{
    protected static string $resource = HariMasukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
