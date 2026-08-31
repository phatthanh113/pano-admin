<?php

namespace App\Filament\Resources\Hotspots\Pages;

use App\Filament\Resources\Hotspots\HotspotResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHotspot extends EditRecord
{
    protected static string $resource = HotspotResource::class;

    public function getTitle(): string
    {
        return __('app.edit_title', ['label' => __('app.hotspots')]);
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
