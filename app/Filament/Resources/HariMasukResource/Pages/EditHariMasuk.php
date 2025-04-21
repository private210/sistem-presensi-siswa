<?php

namespace App\Filament\Resources\HariMasukResource\Pages;

use App\Filament\Resources\HariMasukResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHariMasuk extends EditRecord
{
    protected static string $resource = HariMasukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
