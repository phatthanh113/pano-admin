<?php

namespace App\Filament\Widgets;

use App\Models\Building;
use App\Models\Floor;
use App\Models\Hotspot;
use App\Models\Panorama;
use App\Models\Project;
use App\Models\Video;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PanoStatsWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        return [
            Stat::make(__('app.projects'), Project::count())
                ->description(__('app.total_projects'))
                ->icon(Heroicon::OutlinedBuildingOffice2)
                ->color('primary'),
            Stat::make(__('app.buildings'), Building::count())
                ->description(__('app.total_buildings'))
                ->icon(Heroicon::OutlinedBuildingStorefront)
                ->color('success'),
            Stat::make(__('app.floors'), Floor::count())
                ->description(__('app.total_floors'))
                ->icon(Heroicon::OutlinedSquare3Stack3d)
                ->color('warning'),
            Stat::make(__('app.panoramas'), Panorama::count())
                ->description(__('app.active_panoramas', ['count' => Panorama::where('is_active', true)->count()]))
                ->icon(Heroicon::OutlinedPhoto)
                ->color('info'),
            Stat::make(__('app.hotspots'), Hotspot::count())
                ->description(__('app.total_hotspots'))
                ->icon(Heroicon::OutlinedMapPin)
                ->color('danger'),
            Stat::make(__('app.videos'), Video::count())
                ->description(__('app.total_videos'))
                ->icon(Heroicon::OutlinedVideoCamera)
                ->color('gray'),
        ];
    }
}
