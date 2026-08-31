<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateProject extends CreateRecord
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
        return __('app.create_title', ['label' => __('app.projects')]);
    }

    protected static string $resource = ProjectResource::class;
}
