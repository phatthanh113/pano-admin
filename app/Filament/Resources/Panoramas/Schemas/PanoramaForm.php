<?php

namespace App\Filament\Resources\Panoramas\Schemas;

use App\Models\Building;
use App\Models\Floor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\Storage;

class PanoramaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(fn () => __('forms.section_link'))
                    ->columns(3)
                    ->components([
                        Select::make('project_id')
                            ->label(fn () => __('forms.project'))
                            ->relationship('project', 'name')
                            ->searchable()->preload()
                            ->live(),
                        Select::make('building_id')
                            ->label(fn () => __('forms.building'))
                            ->relationship('building', 'name')
                            ->searchable()->preload()
                            ->live()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('floor_id', null)),
                        Select::make('floor_id')
                            ->label(fn () => __('forms.floor_group'))
                            ->options(function (Get $get) {
                                $bid = $get('building_id');
                                if (! $bid) return [];
                                $building = Building::find($bid);
                                if ($building && $building->type !== 'group') return [];
                                return Floor::where('building_id', $bid)->pluck('name', 'id');
                            })
                            ->searchable()->preload()
                            ->live()
                            ->visible(function (Get $get) {
                                $bid = $get('building_id');
                                if (! $bid) return false;
                                return Building::find($bid)?->type === 'group';
                            }),
                    ]),

                Section::make(fn () => __('forms.section_panorama_info'))
                    ->columns(2)
                    ->components([
                        TextInput::make('slug')->required()->columnSpan(1),
                        TextInput::make('name')->label(fn () => __('forms.name'))->required()->columnSpan(1),
                        TextInput::make('code')->label('Code')->placeholder('VD: 南西面'),
                        TextInput::make('number')->numeric()->label(fn () => __('forms.number')),
                        TextInput::make('label')->label(fn () => __('forms.label'))->columnSpanFull(),
                        FileUpload::make('thumbnail')
                            ->label(fn () => __('forms.thumbnail'))
                            ->image()
                            ->disk('public')->visibility('public')->directory('panoramas/thumbnails')->maxSize(5120)->imagePreviewHeight('150')->columnSpan(1),
                        FileUpload::make('url')->label(fn () => __('forms.panorama_image'))->image()->disk('public')->visibility('public')->directory('panoramas')->maxSize(10240)->imagePreviewHeight('150')->required()->columnSpan(1),
                    ]),

                Section::make(fn () => __('forms.section_map'))
                    ->columnSpanFull()
                    ->columns(1)
                    ->components([
                        View::make('filament.forms.components.panorama-map-picker')
                            ->view('filament.forms.components.panorama-map-picker')
                            ->columnSpanFull()
                            ->viewData(function (Get $get) {
                                $buildingId = $get('building_id');
                                $floorId = $get('floor_id');
                                $plan = null;
                                $label = null;
                                if ($floorId) {
                                    $floor = Floor::find($floorId);
                                    $plan = $floor?->plan_image;
                                    $label = $floor?->name;
                                } elseif ($buildingId) {
                                    $building = Building::find($buildingId);
                                    $plan = $building?->plan_image;
                                    $label = $building?->name;
                                }
                                $planUrl = null;
                                if ($plan) {
                                    if (str_starts_with($plan, 'http') || str_starts_with($plan, '/storage')) {
                                        $planUrl = $plan;
                                    } elseif (str_starts_with($plan, '/images') || str_starts_with($plan, '/maps')) {
                                        // seeded data trong public - giữ nguyên
                                        $planUrl = $plan;
                                    } elseif (str_starts_with($plan, 'buildings/') || str_starts_with($plan, 'floor-plans/') || str_starts_with($plan, 'panoramas/')) {
                                        $planUrl = Storage::disk('public')->url($plan);
                                    } else {
                                        $planUrl = Storage::disk('public')->url($plan);
                                        // fallback nếu file không tồn tại trên public thì thử raw
                                        if (! Storage::disk('public')->exists($plan)) {
                                            $planUrl = $plan;
                                        }
                                    }
                                }
                                return [
                                    'planUrl' => $planUrl,
                                    'planLabel' => $label ?? 'Chưa chọn',
                                ];
                            })
                            ->columnSpanFull(),

                        Grid::make(3)
                            ->columnSpanFull()
                            ->extraAttributes(['class' => 'mt-2'])
                            ->components([
                                TextInput::make('map_x')->label(fn () => __('forms.map_x'))->numeric()->step(0.1)->suffix('%'),
                                TextInput::make('map_y')->label(fn () => __('forms.map_y'))->numeric()->step(0.1)->suffix('%'),
                                TextInput::make('map_angle')->label(fn () => __('forms.map_angle'))->numeric()->step(1)->suffix('°'),
                            ]),
                    ]),

                Section::make(fn () => __('forms.section_default_view'))
                    ->columnSpanFull()
                    ->columns(3)
                    ->components([
                        TextInput::make('yaw')->label(fn () => __('forms.yaw'))->required()->numeric()->default(0),
                        TextInput::make('pitch')->label(fn () => __('forms.pitch'))->required()->numeric()->default(0),
                        TextInput::make('sort_order')->label(fn () => __('forms.sort_order'))->required()->numeric()->default(0),
                        Toggle::make('is_active')->label(fn () => __('forms.is_active'))->required()->columnSpanFull(),
                    ]),
            ]);
    }
}
