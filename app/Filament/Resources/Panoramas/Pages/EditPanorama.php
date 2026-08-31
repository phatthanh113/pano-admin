<?php

namespace App\Filament\Resources\Panoramas\Pages;

use App\Filament\Resources\Panoramas\PanoramaResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPanorama extends EditRecord
{
    protected static string $resource = PanoramaResource::class;

    public function getTitle(): string
    {
        return __('app.edit_title', ['label' => __('app.panoramas')]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label(fn () => __('app.back') !== 'app.back' ? __('app.back') : 'Back')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->extraAttributes(['onclick' => 'history.back(); return false;'])
                ->url('javascript:history.back()'),
            DeleteAction::make(),
        ];
    }
}
