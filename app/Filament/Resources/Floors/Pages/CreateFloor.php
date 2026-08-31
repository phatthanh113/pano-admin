<?php

namespace App\Filament\Resources\Floors\Pages;

use App\Filament\Resources\Floors\FloorResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateFloor extends CreateRecord
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
        return __('app.create_title', ['label' => __('app.floors')]);
    }

    protected static string $resource = FloorResource::class;
}
