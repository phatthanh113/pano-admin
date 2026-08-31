<?php

namespace App\Filament\Resources\Panoramas\Pages;

use App\Filament\Resources\Panoramas\PanoramaResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreatePanorama extends CreateRecord
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
        return __('app.create_title', ['label' => __('app.panoramas')]);
    }

    protected static string $resource = PanoramaResource::class;
}
