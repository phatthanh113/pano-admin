<?php

namespace App\Filament\Resources\Panoramas;

use App\Filament\Resources\Panoramas\Pages\CreatePanorama;
use App\Filament\Resources\Panoramas\Pages\EditPanorama;
use App\Filament\Resources\Panoramas\Pages\ListPanoramas;
use App\Filament\Resources\Panoramas\Schemas\PanoramaForm;
use App\Filament\Resources\Panoramas\Tables\PanoramasTable;
use App\Models\Panorama;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PanoramaResource extends Resource
{
    protected static ?string $model = Panorama::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return __('app.panoramas');
    }

    public static function form(Schema $schema): Schema
    {
        return PanoramaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PanoramasTable::configure($table);
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
            'index' => ListPanoramas::route('/'),
            'create' => CreatePanorama::route('/create'),
            'edit' => EditPanorama::route('/{record}/edit'),
        ];
    }
}
