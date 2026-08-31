<?php

namespace App\Providers;

use Filament\Support\Facades\FilamentView;
use Filament\Tables\View\TablesRenderHook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $hook = function (array $data = []) {
            return view('filament.tables.reset-filter-icon', $data)->render();
        };
        FilamentView::registerRenderHook(TablesRenderHook::FILTER_INDICATORS, $hook, scopes: \App\Filament\Resources\Buildings\BuildingResource::class);
        FilamentView::registerRenderHook(TablesRenderHook::FILTER_INDICATORS, $hook, scopes: \App\Filament\Resources\Floors\FloorResource::class);
        FilamentView::registerRenderHook(TablesRenderHook::FILTER_INDICATORS, $hook, scopes: \App\Filament\Resources\Panoramas\PanoramaResource::class);
        FilamentView::registerRenderHook(TablesRenderHook::FILTER_INDICATORS, $hook, scopes: \App\Filament\Resources\Hotspots\HotspotResource::class);
    }
}
