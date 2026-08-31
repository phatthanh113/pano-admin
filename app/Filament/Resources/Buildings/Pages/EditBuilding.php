<?php

namespace App\Filament\Resources\Buildings\Pages;

use App\Filament\Resources\Buildings\BuildingResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBuilding extends EditRecord
{
    protected static string $resource = BuildingResource::class;

    public function getTitle(): string
    {
        return __('app.edit_title', ['label' => __('app.buildings')]);
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
