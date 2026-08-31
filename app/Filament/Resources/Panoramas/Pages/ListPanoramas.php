<?php

namespace App\Filament\Resources\Panoramas\Pages;

use App\Filament\Resources\Panoramas\PanoramaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPanoramas extends ListRecords
{
    protected static string $resource = PanoramaResource::class;
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
