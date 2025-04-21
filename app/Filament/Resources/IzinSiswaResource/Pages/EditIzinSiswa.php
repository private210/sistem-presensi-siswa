<?php

namespace App\Filament\Resources\IzinSiswaResource\Pages;

use App\Filament\Resources\IzinSiswaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIzinSiswa extends EditRecord
{
    protected static string $resource = IzinSiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
