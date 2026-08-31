<?php

namespace App\Filament\Resources\Hotspots;

use App\Filament\Resources\Hotspots\Pages\CreateHotspot;
use App\Filament\Resources\Hotspots\Pages\EditHotspot;
use App\Filament\Resources\Hotspots\Pages\ListHotspots;
use App\Filament\Resources\Hotspots\Schemas\HotspotForm;
use App\Filament\Resources\Hotspots\Tables\HotspotsTable;
use App\Models\Hotspot;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HotspotResource extends Resource
{
    protected static ?string $model = Hotspot::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?string $recordTitleAttribute = 'tooltip';

    protected static ?int $navigationSort = 5;

    public static function getNavigationLabel(): string
    {
        return __('app.hotspots');
    }

    public static function form(Schema $schema): Schema
    {
        return HotspotForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HotspotsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHotspots::route('/'),
            'create' => CreateHotspot::route('/create'),
            'edit' => EditHotspot::route('/{record}/edit'),
        ];
    }
}
