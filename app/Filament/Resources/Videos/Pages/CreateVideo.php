<?php

namespace App\Filament\Resources\Videos\Pages;

use App\Filament\Resources\Videos\VideoResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateVideo extends CreateRecord
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
        return __('app.create_title', ['label' => __('app.videos')]);
    }

    protected static string $resource = VideoResource::class;
}
