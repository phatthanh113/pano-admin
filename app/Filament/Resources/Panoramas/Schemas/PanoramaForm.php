<?php

namespace App\Filament\Resources\Panoramas\Schemas;

use App\Models\Building;
use App\Models\Floor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
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

                Section::make(fn () => __('forms.section_hotspots') !== 'forms.section_hotspots' ? __('forms.section_hotspots') : 'Hotspots trong Panorama này')
                    ->description(fn () => __('forms.section_hotspots_desc') !== 'forms.section_hotspots_desc' ? __('forms.section_hotspots_desc') : 'Thêm hotspot ngay trong form này, không cần qua menu Hotspots riêng. Click hotspot sẽ bay tới Target Panorama.')
                    ->columnSpanFull()
                    ->collapsed(false)
                    ->components([
                        View::make('filament.forms.components.hotspot-shared-picker')
                            ->view('filament.forms.components.hotspot-shared-picker')
                            ->columnSpanFull()
                            ->viewData(function (Get $get) {
                                $url = $get('url');
                                if (is_array($url)) $url = $url[0] ?? null;
                                // Handle Livewire TemporaryUploadedFile when creating new panorama
                                if (is_object($url) && method_exists($url, 'temporaryUrl')) {
                                    try { $tmp = $url->temporaryUrl(); return ['panoramaUrl' => $tmp, 'hotspots' => $get('hotspots') ?? []]; } catch (\Throwable $e) {}
                                }
                                if (blank($url) || ! is_string($url)) {
                                    $routeRecord = request()->route('record');
                                    $recordId = null;
                                    if ($routeRecord instanceof \App\Models\Panorama) {
                                        $recordId = $routeRecord->id;
                                    } elseif (is_numeric($routeRecord)) {
                                        $recordId = $routeRecord;
                                    }
                                    if ($recordId) {
                                        $panorama = \App\Models\Panorama::find($recordId);
                                        $url = $panorama?->url;
                                    }
                                }
                                // still try to keep url from livewire data if blank
                                if (blank($url) || ! is_string($url)) {
                                    // keep existing hotspots but panoramaUrl null => ảnh sẽ mất, nên cố giữ url từ file upload state
                                    return ['panoramaUrl' => null, 'hotspots' => $get('hotspots') ?? []];
                                }
                                $displayUrl = $url;
                                if (str_starts_with($url, 'http') || str_starts_with($url, '/storage')) {
                                    $displayUrl = $url;
                                } elseif (str_starts_with($url, '/images') || str_starts_with($url, '/maps')) {
                                    $displayUrl = $url;
                                } elseif (str_starts_with($url, 'panoramas/')) {
                                    $displayUrl = Storage::disk('public')->url($url);
                                } elseif (Storage::disk('public')->exists($url)) {
                                    $displayUrl = Storage::disk('public')->url($url);
                                }
                                return ['panoramaUrl' => $displayUrl, 'hotspots' => $get('hotspots') ?? []];
                            }),
                        Repeater::make('hotspots')
                            ->relationship()
                            ->label('')
                            ->columns(4)
                            ->collapsible()
                            ->collapsed()
                            ->itemLabel(function (array $state): ?string {
                                // Đếm thứ tự dựa trên tooltip hoặc fallback - JS sẽ đổi lại thành Hotspot 1,2 sau render
                                // Giữ đơn giản: Hotspot 1, Hotspot 2 ... (tooltip sẽ hiện trong form)
                                static $counter = 0;
                                // Không dùng static thực sự vì sẽ tăng mãi, dùng tooltip để phân biệt
                                if (!empty($state['tooltip'])) return $state['tooltip'];
                                if (!empty($state['target_panorama_id'])) return '→ Panorama #'.$state['target_panorama_id'];
                                return 'Hotspot mới';
                            })
                            ->addActionLabel(fn () => __('forms.add_hotspot') !== 'forms.add_hotspot' ? __('forms.add_hotspot') : 'Thêm hotspot')
                            ->reorderable(false)
                            ->components([
                                Select::make('target_panorama_id')
                                    ->label(fn () => __('forms.panorama_target'))
                                    ->relationship('targetPanorama', 'name', modifyQueryUsing: function (\Illuminate\Database\Eloquent\Builder $query, Get $get) {
                                        // Lấy floor/building từ form cha - repeater item cần truy ngược
                                        $floorId = $get('floor_id') ?? $get('../../floor_id') ?? $get('../../../floor_id') ?? null;
                                        $buildingId = $get('building_id') ?? $get('../../building_id') ?? $get('../../../building_id') ?? null;
                                        // Fallback: thử lấy từ Livewire data nếu $get không có (khi repeater chưa kịp sync)
                                        if (blank($floorId) && blank($buildingId)) {
                                            $data = request()->all();
                                            // không dùng request, thử lấy từ record hiện tại
                                            $routeRecord = request()->route('record');
                                            $panorama = null;
                                            if ($routeRecord instanceof \App\Models\Panorama) $panorama = $routeRecord;
                                            elseif (is_numeric($routeRecord)) $panorama = \App\Models\Panorama::find($routeRecord);
                                            if ($panorama) {
                                                $floorId = $panorama->floor_id;
                                                $buildingId = $panorama->building_id;
                                            }
                                        }
                                        if (!empty($floorId)) {
                                            $query->where('floor_id', $floorId);
                                        } elseif (!empty($buildingId)) {
                                            $query->where('building_id', $buildingId);
                                            // nếu building là group thì panorama phải có floor_id thuộc building đó, không lọc floor_id null cứng
                                            $building = \App\Models\Building::find($buildingId);
                                            if ($building && $building->type === 'group') {
                                                // chỉ lấy panorama thuộc building này (kể cả có floor)
                                                $query->where('building_id', $buildingId);
                                            } else {
                                                $query->where('building_id', $buildingId)->whereNull('floor_id');
                                            }
                                        }
                                        // Loại panorama hiện tại ra khỏi danh sách đích
                                        $routeRecord = request()->route('record');
                                        $currentId = null;
                                        if ($routeRecord instanceof \App\Models\Panorama) $currentId = $routeRecord->id;
                                        elseif (is_numeric($routeRecord)) $currentId = $routeRecord;
                                        if ($currentId) $query->where('id', '!=', $currentId);
                                        $query->where('is_active', true);
                                    })
                                    ->getOptionLabelFromRecordUsing(fn (\App\Models\Panorama $record) => $record->name . ' — ' . ($record->building?->name ?? '-') . ($record->floor ? '/' . $record->floor->name : '') . ' #' . $record->id)
                                    ->searchable(['name', 'slug'])
                                    ->preload()
                                    ->required()
                                    ->columnSpan(1),
                                TextInput::make('tooltip')
                                    ->label(fn () => __('forms.tooltip'))
                                    ->placeholder(fn () => __('forms.tooltip_placeholder'))
                                    ->columnSpan(1),
                                TextInput::make('yaw')
                                    ->label(fn () => __('forms.yaw_horizontal'))
                                    ->required()->numeric()->step(0.1)->default(0)->suffix('°')
                                    ->columnSpan(1),
                                TextInput::make('pitch')
                                    ->label(fn () => __('forms.pitch_vertical'))
                                    ->required()->numeric()->step(0.1)->default(0)->suffix('°')
                                    ->columnSpan(1),
                                \Filament\Forms\Components\Hidden::make('sort_order')->default(0),
                            ]),
                    ]),
            ]);
    }
}
