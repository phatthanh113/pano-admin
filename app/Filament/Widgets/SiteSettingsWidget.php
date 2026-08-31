<?php

namespace App\Filament\Widgets;

use App\Models\SiteSetting;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Storage;

class SiteSettingsWidget extends Widget
{
    protected string $view = 'filament.widgets.site-settings-widget';

    protected int|string|array $columnSpan = 1;

    protected static ?int $sort = -1;

    public function getViewData(): array
    {
        $setting = SiteSetting::current();
        return [
            'setting' => $setting,
            'logoUrl' => $setting->logo_url,
        ];
    }
}
