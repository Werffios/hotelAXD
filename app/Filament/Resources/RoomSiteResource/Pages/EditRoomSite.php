<?php

namespace App\Filament\Resources\RoomSiteResource\Pages;

use App\Filament\Resources\RoomSiteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRoomSite extends EditRecord
{
    protected static string $resource = RoomSiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
