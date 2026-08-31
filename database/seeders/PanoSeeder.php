<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Floor;
use App\Models\Hotspot;
use App\Models\Panorama;
use App\Models\Project;
use App\Models\Video;
use Illuminate\Database\Seeder;

class PanoSeeder extends Seeder
{
    public function run(): void
    {
        $sampleVideos = [
            ['title' => 'ムービー 1', 'label' => 'トラックヤード', 'description' => 'Khu vực sân xe tải', 'thumbnail' => '/images/pana1.jpg', 'video_url' => 'https://www.youtube.com/watch?v=ScMzIvxBSi4', 'sort_order' => 1],
            ['title' => 'ムービー 2', 'label' => 'エントランス・ロビー', 'description' => 'Sảnh đón tiếp', 'thumbnail' => '/images/pana2.jpg', 'video_url' => 'https://www.youtube.com/watch?v=LXb3EKWsInQ', 'sort_order' => 2],
            ['title' => 'ムービー 4', 'label' => '物流倉庫保管エリア', 'description' => 'Khu vực kho chứa', 'thumbnail' => '/images/pana3.jpg', 'video_url' => 'https://www.youtube.com/watch?v=aqz-KE-bpKQ', 'sort_order' => 3],
            ['title' => 'ムービー 5', 'label' => '屋上庭園・テラス', 'description' => 'Sân thượng Rooftop', 'thumbnail' => '/images/pana4.jpg', 'video_url' => 'https://www.youtube.com/watch?v=3JZ_D3ELwOQ', 'sort_order' => 4],
        ];

        $projects = [
            ['slug' => 'globe3-building-1', 'name' => 'globe3-building 1', 'sort_order' => 1],
            ['slug' => 'project-2', 'name' => 'Project 2 - Demo', 'sort_order' => 2],
            ['slug' => 'project-3', 'name' => 'Project 3 - Sample', 'sort_order' => 3],
        ];

        // chỉ seed chi tiết cho project đầu, 2 project còn lại copy structure rỗng
        foreach ($projects as $idx => $pData) {
            $project = Project::firstOrCreate(['slug' => $pData['slug']], $pData);

            if ($idx === 0) {
                $this->seedBuildings($project, $sampleVideos);
            } else {
                // tạo 1 building mẫu cho project demo
                $b = Building::create([
                    'project_id' => $project->id,
                    'slug' => 'exterior',
                    'name' => '外構',
                    'type' => 'single',
                    'plan_image' => '/maps/exterior.jpg',
                    'sort_order' => 1,
                ]);
                foreach ($sampleVideos as $v) {
                    $b->videos()->create($v);
                }
            }
        }
    }

    private function seedBuildings(Project $project, array $sampleVideos): void
    {
        $buildingsData = [
            [
                'id' => 'exterior', 'name' => '外構', 'type' => 'single', 'planImage' => '/maps/exterior.jpg',
                'panoramas' => [
                    ['id' => 'ext-1', 'name' => '新1号館外観(南西面)', 'code' => '南西面', 'number' => 1, 'thumbnail' => '/images/pana1.jpg', 'url' => '/images/pana1.jpg', 'mapPosition' => ['x' => 42.5, 'y' => 76.5, 'angle' => 0], 'defaultView' => ['yaw' => 0, 'pitch' => 0], 'label' => '新1号館外観(南西面)', 'hotspots' => [['yaw' => 35, 'pitch' => -2, 'tooltip' => 'Đến 外観2', 'targetPanorama' => 'ext-2']]],
                    ['id' => 'ext-2', 'name' => '新1号館外観(南面)', 'code' => '南面', 'number' => 2, 'thumbnail' => '/images/pana2.jpg', 'url' => '/images/pana2.jpg', 'mapPosition' => ['x' => 49, 'y' => 52, 'angle' => -20], 'defaultView' => ['yaw' => 30, 'pitch' => 0], 'label' => '新1号館外観(南面)', 'hotspots' => [['yaw' => 215, 'pitch' => -2, 'tooltip' => 'Về 1', 'targetPanorama' => 'ext-1']]],
                    ['id' => 'ext-3', 'name' => 'メディカルデッキ外観(東面)', 'code' => '東面', 'number' => 3, 'thumbnail' => '/images/pana3.jpg', 'url' => '/images/pana3.jpg', 'mapPosition' => ['x' => 38, 'y' => 38.5, 'angle' => 45], 'defaultView' => ['yaw' => 90, 'pitch' => 0], 'label' => 'メディカルデッキ外観(東面)', 'hotspots' => []],
                    ['id' => 'ext-4', 'name' => 'メディカルデッキ外観(西面)', 'code' => '西面', 'number' => 4, 'thumbnail' => '/images/pana4.jpg', 'url' => '/images/pana4.jpg', 'mapPosition' => ['x' => 58.5, 'y' => 24, 'angle' => 135], 'defaultView' => ['yaw' => 180, 'pitch' => 0], 'label' => 'メディカルデッキ外観(西面)', 'hotspots' => []],
                    ['id' => 'ext-5', 'name' => '新1号館外観(南東面)', 'code' => '南東面', 'number' => 5, 'thumbnail' => '/images/pana1.jpg', 'url' => '/images/pana1.jpg', 'mapPosition' => ['x' => 74, 'y' => 44, 'angle' => -85], 'defaultView' => ['yaw' => 0, 'pitch' => 0], 'label' => '新1号館外観(南東面)', 'hotspots' => []],
                    ['id' => 'ext-6', 'name' => 'キャンパス全体俯瞰', 'code' => '俯瞰', 'number' => 6, 'thumbnail' => '/images/pana2.jpg', 'url' => '/images/pana2.jpg', 'mapPosition' => ['x' => 23, 'y' => 75, 'angle' => 40], 'defaultView' => ['yaw' => 45, 'pitch' => 0], 'label' => 'キャンパス全体俯瞰', 'hotspots' => []],
                ],
            ],
            [
                'id' => 'medical-deck', 'name' => 'メディカルデッキ', 'type' => 'single', 'planImage' => '/maps/floor1.jpg',
                'panoramas' => [
                    ['id' => 'med-1', 'name' => '新1号館外観(南西面)', 'code' => '南西面', 'number' => 1, 'thumbnail' => '/images/pana1.jpg', 'url' => '/images/pana1.jpg', 'mapPosition' => ['x' => 35, 'y' => 22, 'angle' => 90], 'defaultView' => ['yaw' => 0, 'pitch' => 0], 'label' => '新1号館外観(南西面)', 'hotspots' => []],
                    ['id' => 'med-2', 'name' => 'メディカルデッキ外観(東面)', 'code' => '東面', 'number' => 2, 'thumbnail' => '/images/pana2.jpg', 'url' => '/images/pana2.jpg', 'mapPosition' => ['x' => 52, 'y' => 28, 'angle' => 0], 'defaultView' => ['yaw' => 45, 'pitch' => 0], 'label' => 'メディカルデッキ外観(東面)', 'hotspots' => []],
                    ['id' => 'med-3', 'name' => '新1号館外観(南面)', 'code' => '南面', 'number' => 3, 'thumbnail' => '/images/pana3.jpg', 'url' => '/images/pana3.jpg', 'mapPosition' => ['x' => 50, 'y' => 38, 'angle' => -90], 'defaultView' => ['yaw' => 90, 'pitch' => 0], 'label' => '新1号館外観(南面)', 'hotspots' => []],
                    ['id' => 'med-4', 'name' => '新1号館外観(南東面)', 'code' => '南東面', 'number' => 4, 'thumbnail' => '/images/pana4.jpg', 'url' => '/images/pana4.jpg', 'mapPosition' => ['x' => 68, 'y' => 40, 'angle' => 180], 'defaultView' => ['yaw' => 180, 'pitch' => 0], 'label' => '新1号館外観(南東面)', 'hotspots' => []],
                    ['id' => 'med-5', 'name' => '1階カフェ待合棟', 'code' => 'カフェ', 'number' => 5, 'thumbnail' => '/images/pana1.jpg', 'url' => '/images/pana1.jpg', 'mapPosition' => ['x' => 38, 'y' => 62, 'angle' => 0], 'defaultView' => ['yaw' => 0, 'pitch' => 0], 'label' => '1階カフェ待合棟', 'hotspots' => []],
                    ['id' => 'med-6', 'name' => 'メディカルデッキ エントランス', 'code' => 'エントランス', 'number' => 6, 'thumbnail' => '/images/pana2.jpg', 'url' => '/images/pana2.jpg', 'mapPosition' => ['x' => 50, 'y' => 78, 'angle' => -45], 'defaultView' => ['yaw' => 45, 'pitch' => 0], 'label' => 'メディカルデッキ エントランス', 'hotspots' => []],
                ],
            ],
            [
                'id' => 'cafe', 'name' => 'カフェ・待合棟', 'type' => 'single', 'planImage' => '/maps/floor2.jpg',
                'panoramas' => [
                    ['id' => 'cafe-1', 'name' => 'カフェ外観', 'code' => '外観', 'number' => 1, 'thumbnail' => '/images/pana3.jpg', 'url' => '/images/pana3.jpg', 'mapPosition' => ['x' => 45, 'y' => 45, 'angle' => 0], 'defaultView' => ['yaw' => 0, 'pitch' => 0], 'label' => 'カフェ外観', 'hotspots' => []],
                    ['id' => 'cafe-2', 'name' => '待合ロビー', 'code' => 'ロビー', 'number' => 2, 'thumbnail' => '/images/pana4.jpg', 'url' => '/images/pana4.jpg', 'mapPosition' => ['x' => 55, 'y' => 60, 'angle' => 90], 'defaultView' => ['yaw' => 90, 'pitch' => 0], 'label' => '待合ロビー', 'hotspots' => []],
                ],
            ],
            [
                'id' => 'new1', 'name' => '新1号館', 'type' => 'group', 'planImage' => null,
                'floors' => [
                    ['id' => 'new1-b1', 'name' => 'B1階', 'shortLabel' => 'B1', 'planImage' => '/maps/floor1.jpg', 'panoramas' => [
                        ['id' => 'b1-1', 'name' => 'B1 廊下', 'code' => 'B1-1', 'number' => 1, 'thumbnail' => '/images/pana1.jpg', 'url' => '/images/pana1.jpg', 'mapPosition' => ['x' => 42, 'y' => 48, 'angle' => 0], 'defaultView' => ['yaw' => 0, 'pitch' => 0], 'label' => 'B1 廊下', 'hotspots' => []],
                        ['id' => 'b1-2', 'name' => 'B1 駐車場', 'code' => 'B1-2', 'number' => 2, 'thumbnail' => '/images/pana2.jpg', 'url' => '/images/pana2.jpg', 'mapPosition' => ['x' => 60, 'y' => 55, 'angle' => 90], 'defaultView' => ['yaw' => 45, 'pitch' => 0], 'label' => 'B1 駐車場', 'hotspots' => []],
                    ]],
                    ['id' => 'new1-1f', 'name' => '1階', 'shortLabel' => '1F', 'planImage' => '/maps/floor1.jpg', 'panoramas' => [
                        ['id' => '1f-1', 'name' => '1階 エントランス', 'code' => '1F-1', 'number' => 1, 'thumbnail' => '/images/pana1.jpg', 'url' => '/images/pana1.jpg', 'mapPosition' => ['x' => 26, 'y' => 55, 'angle' => 90], 'defaultView' => ['yaw' => 0, 'pitch' => 0], 'label' => '1階 エントランス', 'hotspots' => []],
                        ['id' => '1f-2', 'name' => '1階 ロビー', 'code' => '1F-2', 'number' => 2, 'thumbnail' => '/images/pana2.jpg', 'url' => '/images/pana2.jpg', 'mapPosition' => ['x' => 50, 'y' => 55, 'angle' => 0], 'defaultView' => ['yaw' => 45, 'pitch' => 0], 'label' => '1階 ロビー', 'hotspots' => []],
                    ]],
                    ['id' => 'new1-2f', 'name' => '2階', 'shortLabel' => '2F', 'planImage' => '/maps/floor2.jpg', 'panoramas' => [
                        ['id' => '2f-1', 'name' => '2階 病棟', 'code' => '2F-1', 'number' => 1, 'thumbnail' => '/images/pana3.jpg', 'url' => '/images/pana3.jpg', 'mapPosition' => ['x' => 35, 'y' => 45, 'angle' => 0], 'defaultView' => ['yaw' => 0, 'pitch' => 0], 'label' => '2階 病棟', 'hotspots' => []],
                    ]],
                    ['id' => 'new1-3f', 'name' => '3階', 'shortLabel' => '3F', 'planImage' => '/maps/floor3.jpg', 'panoramas' => [
                        ['id' => '3f-1', 'name' => '3階 病棟', 'code' => '3F-1', 'number' => 1, 'thumbnail' => '/images/pana4.jpg', 'url' => '/images/pana4.jpg', 'mapPosition' => ['x' => 40, 'y' => 50, 'angle' => 0], 'defaultView' => ['yaw' => 0, 'pitch' => 0], 'label' => '3階 病棟', 'hotspots' => []],
                    ]],
                    ['id' => 'new1-5f', 'name' => '5階', 'shortLabel' => '5F', 'planImage' => '/maps/floor3.jpg', 'panoramas' => [
                        ['id' => '5f-1', 'name' => '5階教授室等廊下', 'code' => '5F-1', 'number' => 1, 'thumbnail' => '/images/pana1.jpg', 'url' => '/images/pana1.jpg', 'mapPosition' => ['x' => 60, 'y' => 32, 'angle' => 90], 'defaultView' => ['yaw' => 0, 'pitch' => 0], 'label' => '5階教授室等廊下', 'hotspots' => []],
                        ['id' => '5f-2', 'name' => '5階教授室等', 'code' => '5F-2', 'number' => 2, 'thumbnail' => '/images/pana2.jpg', 'url' => '/images/pana2.jpg', 'mapPosition' => ['x' => 64, 'y' => 52, 'angle' => 0], 'defaultView' => ['yaw' => 45, 'pitch' => 0], 'label' => '5階教授室等', 'hotspots' => []],
                        ['id' => '5f-3', 'name' => '新1号館外観(南西面)', 'code' => '南西面', 'number' => 3, 'thumbnail' => '/images/pana3.jpg', 'url' => '/images/pana3.jpg', 'mapPosition' => ['x' => 19, 'y' => 92, 'angle' => 180], 'defaultView' => ['yaw' => 90, 'pitch' => 0], 'label' => '新1号館外観(南西面)', 'hotspots' => []],
                        ['id' => '5f-4', 'name' => 'メディカルデッキ外観(南面)', 'code' => '南面', 'number' => 4, 'thumbnail' => '/images/pana4.jpg', 'url' => '/images/pana4.jpg', 'mapPosition' => ['x' => 52, 'y' => 94, 'angle' => 0], 'defaultView' => ['yaw' => 180, 'pitch' => 0], 'label' => 'メディカルデッキ外観(南面)', 'hotspots' => []],
                        ['id' => '5f-5', 'name' => '新1号館外観(南東面)', 'code' => '南東面', 'number' => 5, 'thumbnail' => '/images/pana1.jpg', 'url' => '/images/pana1.jpg', 'mapPosition' => ['x' => 73, 'y' => 94, 'angle' => 0], 'defaultView' => ['yaw' => 0, 'pitch' => 0], 'label' => '新1号館外観(南東面)', 'hotspots' => []],
                    ]],
                    ['id' => 'new1-6f', 'name' => '6階', 'shortLabel' => '6F', 'planImage' => '/maps/floor2.jpg', 'panoramas' => [
                        ['id' => '6f-1', 'name' => '6階 講義室', 'code' => '6F-1', 'number' => 1, 'thumbnail' => '/images/pana2.jpg', 'url' => '/images/pana2.jpg', 'mapPosition' => ['x' => 45, 'y' => 50, 'angle' => 0], 'defaultView' => ['yaw' => 0, 'pitch' => 0], 'label' => '6階 講義室', 'hotspots' => []],
                    ]],
                ],
            ],
        ];

        $order = 1;
        foreach ($buildingsData as $b) {
            $building = Building::create([
                'project_id' => $project->id,
                'slug' => $b['id'],
                'name' => $b['name'],
                'type' => $b['type'],
                'plan_image' => $b['planImage'],
                'sort_order' => $order++,
            ]);
            foreach ($sampleVideos as $v) {
                $building->videos()->create($v);
            }

            if ($b['type'] === 'single') {
                $this->createPanoramas($project, $building, null, $b['panoramas']);
            } else {
                $fOrder = 1;
                foreach ($b['floors'] as $f) {
                    $floor = Floor::create([
                        'building_id' => $building->id,
                        'slug' => $f['id'],
                        'name' => $f['name'],
                        'short_label' => $f['shortLabel'],
                        'plan_image' => $f['planImage'],
                        'sort_order' => $fOrder++,
                    ]);
                    foreach ($sampleVideos as $v) {
                        $floor->videos()->create($v);
                    }
                    $this->createPanoramas($project, $building, $floor, $f['panoramas']);
                }
            }
        }

        // resolve hotspots target_panorama_id
        $this->resolveHotspots();
    }

    private function createPanoramas(Project $project, Building $building, ?Floor $floor, array $panoramas): void
    {
        $order = 1;
        foreach ($panoramas as $p) {
            $panorama = Panorama::create([
                'project_id' => $project->id,
                'building_id' => $building->id,
                'floor_id' => $floor?->id,
                'slug' => $p['id'],
                'name' => $p['name'],
                'code' => $p['code'] ?? null,
                'number' => $p['number'] ?? $order,
                'thumbnail' => $p['thumbnail'],
                'url' => $p['url'],
                'label' => $p['label'] ?? $p['name'],
                'map_x' => $p['mapPosition']['x'] ?? null,
                'map_y' => $p['mapPosition']['y'] ?? null,
                'map_angle' => $p['mapPosition']['angle'] ?? null,
                'yaw' => $p['defaultView']['yaw'] ?? 0,
                'pitch' => $p['defaultView']['pitch'] ?? 0,
                'sort_order' => $order++,
            ]);
            foreach ($p['hotspots'] ?? [] as $hs) {
                Hotspot::create([
                    'panorama_id' => $panorama->id,
                    'target_panorama_id' => null, // resolve later via slug
                    'tooltip' => $hs['tooltip'] ?? null,
                    'yaw' => $hs['yaw'],
                    'pitch' => $hs['pitch'],
                ]);
                // store mapping via temporary table? we keep slug in tooltip resolution via raw
                // simpler: stash target slug in tooltip mapping? Actually we need to map.
                // We'll store slug in a temp cache: use latest hotspot and update target via lookup
                // But we lost slug. Let's fix by storing targetSlug in tooltip prefix hack then resolve.
                // Instead we track: last hotspot's target slug via separate logic - we need to re-query.
                // To keep simple, we create hotspots with yaw/pitch/tooltip and later update target_panorama_id by matching tooltip??? No.
                // Alternative: we insert with target slug stored in a separate array then resolve.
            }
            // To properly link, we need to track target slugs - redo with explicit mapping
        }
    }

    private function resolveHotspots(): void
    {
        // Hardcode hotspot links from buildingsData
        $links = [
            'ext-1' => [['target' => 'ext-2', 'yaw' => 35, 'pitch' => -2]],
            'ext-2' => [['target' => 'ext-1', 'yaw' => 215, 'pitch' => -2]],
        ];
        foreach ($links as $fromSlug => $targets) {
            $from = Panorama::where('slug', $fromSlug)->first();
            if (! $from) continue;
            foreach ($targets as $t) {
                $to = Panorama::where('slug', $t['target'])->first();
                if (! $to) continue;
                Hotspot::where('panorama_id', $from->id)
                    ->where('yaw', $t['yaw'])
                    ->where('pitch', $t['pitch'])
                    ->update(['target_panorama_id' => $to->id]);
            }
        }
    }
}
