<?php

namespace App\Filament\Resources\Buildings\Pages;

use App\Filament\Resources\Buildings\BuildingResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateBuilding extends CreateRecord
{
    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label(fn () => __('app.back') !== 'app.back' ? __('app.back') : 'Back')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->extraAttributes(['onclick' => 'history.back(); return false;'])
                ->url('javascript:history.back()')
        ];
    }

    public function getTitle(): string
    {
        return __('app.create_title', ['label' => __('app.buildings')]);
    }

    protected static string $resource = BuildingResource::class;
}
