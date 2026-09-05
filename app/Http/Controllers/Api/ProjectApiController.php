<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ProjectApiController extends Controller
{
    /**
     * GET /api/projects
     * Trả về toàn bộ dữ liệu dạng cây để frontend render trực tiếp
     * Format khớp với buildingsData.js để frontend drop-in thay thế
     */
    public function index(): JsonResponse
    {
        $projects = Project::with([
            'buildings' => function ($q) {
                $q->where('is_active', true)->orderBy('sort_order');
            },
            'buildings.floors' => function ($q) {
                $q->where('is_active', true)->orderBy('sort_order');
            },
            'buildings.floors.panoramas' => function ($q) {
                $q->where('is_active', true)->orderBy('sort_order');
            },
            'buildings.floors.panoramas.hotspots',
            'buildings.floors.videos' => function ($q) {
                $q->where('is_active', true)->orderBy('sort_order');
            },
            'buildings.panoramas' => function ($q) {
                $q->where('is_active', true)->orderBy('sort_order');
            },
            'buildings.panoramas.hotspots',
            'buildings.videos' => function ($q) {
                $q->where('is_active', true)->orderBy('sort_order');
            },
            // building tách rời: nếu project không có building, vẫn lấy panorama trực tiếp của project để tạo virtual building
            'panoramas' => function ($q) {
                $q->where('is_active', true)->whereNull('building_id')->whereNull('floor_id')->orderBy('sort_order');
            },
            'panoramas.hotspots',
        ])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $data = $projects->map(function (Project $project) {
            // building tách rời: nếu project không có building mà có panorama trực tiếp -> tạo virtual single
            $buildings = $project->buildings;
            if ($buildings->isEmpty() && $project->panoramas->isNotEmpty()) {
                $virtual = (object)[
                    'slug' => $project->slug . '-virtual',
                    'id' => 0,
                    'name' => $project->name,
                    'type' => 'single',
                    'description' => null,
                    'plan_image' => null,
                    'videos' => collect(),
                    'panoramas' => $project->panoramas,
                    '_isVirtual' => true,
                ];
                $buildings = collect([$virtual]);
            }
            return [
                'id' => $project->slug,
                'db_id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'thumbnail' => $this->resolveUrl($project->thumbnail),
                'buildings' => $buildings->map(function ($building) {
                    $base = [
                        'id' => $building->slug,
                        'db_id' => $building->id,
                        'name' => $building->name,
                        'type' => $building->type,
                        'description' => $building->description,
                        'planImage' => $this->resolveUrl($building->plan_image),
                        'videos' => $building->videos->map(fn ($v) => $this->formatVideo($v))->values(),
                    ];

                    if ($building->type === 'single') {
                        $base['panoramas'] = $building->panoramas->map(fn ($p) => $this->formatPanorama($p))->values();
                    } else {
                        $base['floors'] = $building->floors->map(function ($floor) {
                            return [
                                'id' => $floor->slug,
                                'db_id' => $floor->id,
                                'name' => $floor->name,
                                'shortLabel' => $floor->short_label,
                                'description' => $floor->description,
                                'planImage' => $this->resolveUrl($floor->plan_image),
                                'videos' => $floor->videos->map(fn ($v) => $this->formatVideo($v))->values(),
                                'panoramas' => $floor->panoramas->map(fn ($p) => $this->formatPanorama($p))->values(),
                            ];
                        })->values();
                    }

                    return $base;
                })->values(),
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'total_projects' => $projects->count(),
            ],
        ]);
    }

    /**
     * GET /api/projects/{slug}
     */
    public function show(string $slug): JsonResponse
    {
        $project = Project::with([
            'buildings.floors.panoramas.hotspots',
            'buildings.floors.videos',
            'buildings.panoramas.hotspots',
            'buildings.videos',
        ])->where('slug', $slug)->firstOrFail();

        // reuse index mapping for single
        $request = request();
        $request->merge(['slug' => $slug]);
        // simple re-use: call index and filter - or map single
        // For performance, map directly
        $mapped = $this->index()->getData(true)['data'];
        $single = collect($mapped)->firstWhere('id', $slug);

        return response()->json(['data' => $single]);
    }

    /**
     * GET /api/health - check connectivity, tránh CORS debug
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'app' => config('app.name'),
            'time' => now()->toIso8601String(),
        ]);
    }

    private function formatPanorama($p): array
    {
        $extra = [];
        if (!empty($p->extra_images) && is_array($p->extra_images)) {
            foreach ($p->extra_images as $img) {
                if (blank($img)) continue;
                $extra[] = $this->resolveUrl($img);
            }
        }
        return [
            'id' => $p->slug,
            'db_id' => $p->id,
            'name' => $p->name,
            'code' => $p->code,
            'number' => $p->number,
            'label' => $p->label,
            'thumbnail' => $this->resolveUrl($p->thumbnail),
            'url' => $this->resolveUrl($p->url),
            'extraImages' => $extra,
            'extra_images' => $extra, // snake alias cho frontend cũ
            'mapPosition' => [
                'x' => $p->map_x !== null ? (float) $p->map_x : null,
                'y' => $p->map_y !== null ? (float) $p->map_y : null,
                'angle' => $p->map_angle !== null ? (float) $p->map_angle : 0,
            ],
            'defaultView' => [
                'yaw' => $p->yaw !== null ? (float) $p->yaw : 0,
                'pitch' => $p->pitch !== null ? (float) $p->pitch : 0,
            ],
            'hotspots' => $p->hotspots->map(fn ($h) => [
                'id' => 'hs-' . $h->id,
                'db_id' => $h->id,
                'yaw' => (float) $h->yaw,
                'pitch' => (float) $h->pitch,
                'tooltip' => $h->tooltip,
                'targetPanorama' => $h->targetPanorama?->slug,
                'target_panorama_id' => $h->target_panorama_id,
            ])->values(),
        ];
    }

    private function formatVideo($v): array
    {
        return [
            'id' => 'video-' . $v->id,
            'db_id' => $v->id,
            'title' => $v->title,
            'label' => $v->label,
            'description' => $v->description,
            'thumbnail' => $this->resolveUrl($v->thumbnail),
            'videoUrl' => $v->video_url,
        ];
    }

    private function resolveUrl(?string $path): ?string
    {
        if (blank($path)) return null;
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '//')) {
            return $path;
        }
        if (str_starts_with($path, '/')) {
            // legacy absolute path stored in DB e.g. /images/pana1.jpg -> keep as is, frontend will resolve via proxy
            return $path;
        }
        // storage path e.g. panoramas/xxx.jpg -> /storage/panoramas/xxx.jpg
        return Storage::disk('public')->url($path);
    }
}
