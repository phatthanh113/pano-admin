<?php

namespace App\Filament\Resources\Hotspots\Schemas;

use App\Models\Panorama;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;

class HotspotForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(fn () => __('forms.section_basic_info'))
                    ->columns(2)
                    ->components([
                        Select::make('panorama_id')
                            ->label(fn () => __('forms.panorama_source'))
                            ->relationship('panorama', 'name')
                            ->getOptionLabelFromRecordUsing(fn (Panorama $record) => $record->name . ' — ' . ($record->building?->name ?? '-') . ($record->floor ? '/' . $record->floor->name : '') . ' #' . $record->id)
                            ->searchable(['name', 'slug'])
                            ->preload()
                            ->required()
                            ->live(),
                        Select::make('target_panorama_id')
                            ->label(fn () => __('forms.panorama_target'))
                            ->relationship('targetPanorama', 'name')
                            ->getOptionLabelFromRecordUsing(fn (Panorama $record) => $record->name . ' — ' . ($record->building?->name ?? '-') . ($record->floor ? '/' . $record->floor->name : '') . ' #' . $record->id)
                            ->searchable(['name', 'slug'])
                            ->preload(),
                        TextInput::make('tooltip')
                            ->label(fn () => __('forms.tooltip'))
                            ->placeholder(fn () => __('forms.tooltip_placeholder'))
                            ->columnSpanFull(),
                        TextInput::make('sort_order')
                            ->label(fn () => __('forms.sort_order'))
                            ->numeric()
                            ->default(0)
                            ->required(),
                    ]),

                Section::make(fn () => __('forms.section_hotspot_position'))
                    ->components([
                        View::make('filament.forms.components.hotspot-picker')
                            ->view('filament.forms.components.hotspot-picker')
                            ->viewData(function (Get $get) {
                                $id = $get('panorama_id');
                                $panorama = $id ? Panorama::find($id) : null;
                                $url = $panorama?->url;
                                $displayUrl = 'https://via.placeholder.com/800x400?text=Chua+chon+panorama';
                                if ($url) {
                                    if (str_starts_with($url, 'http') || str_starts_with($url, '/storage')) {
                                        $displayUrl = $url;
                                    } elseif (str_starts_with($url, '/images') || str_starts_with($url, '/maps')) {
                                        $displayUrl = $url;
                                    } elseif (str_starts_with($url, 'panoramas/')) {
                                        $displayUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($url);
                                    } elseif (\Illuminate\Support\Facades\Storage::disk('public')->exists($url)) {
                                        $displayUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($url);
                                    } else {
                                        $displayUrl = $url;
                                    }
                                }
                                return [
                                    'panorama' => $panorama,
                                    'displayUrl' => $displayUrl,
                                ];
                            })
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->components([
                                TextInput::make('yaw')
                                    ->label(fn () => __('forms.yaw_horizontal'))
                                    ->required()
                                    ->numeric()
                                    ->step(0.1)
                                    ->default(0)
                                    ->suffix('°'),
                                TextInput::make('pitch')
                                    ->label(fn () => __('forms.pitch_vertical'))
                                    ->required()
                                    ->numeric()
                                    ->step(0.1)
                                    ->default(0)
                                    ->suffix('°'),
                            ]),
                    ]),
            ]);
    }
}
