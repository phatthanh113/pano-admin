<?php

namespace App\Filament\Resources\Hotspots\Pages;

use App\Filament\Resources\Hotspots\HotspotResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHotspots extends ListRecords
{
    protected static string $resource = HotspotResource::class;
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
