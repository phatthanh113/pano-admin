<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Panorama extends Model
{
    protected $fillable = [
        'project_id', 'building_id', 'floor_id',
        'slug', 'name', 'code', 'number', 'thumbnail', 'url', 'extra_images', 'label',
        'map_x', 'map_y', 'map_angle', 'yaw', 'pitch', 'sort_order', 'is_active'
    ];

    protected $casts = [
        'map_x' => 'decimal:2',
        'map_y' => 'decimal:2',
        'map_angle' => 'decimal:2',
        'yaw' => 'decimal:2',
        'pitch' => 'decimal:2',
        'is_active' => 'boolean',
        'extra_images' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $panorama) {
            // nếu upload panorama image mà chưa có thumbnail thì tự copy làm thumbnail
            if (blank($panorama->thumbnail) && filled($panorama->url)) {
                $src = $panorama->url;
                // chỉ xử lý khi src là file trong storage panoramas
                if (! str_starts_with($src, 'http') && ! str_starts_with($src, '/')) {
                    $thumbPath = 'panoramas/thumbnails/'.basename($src);
                    if (Storage::disk('public')->exists($src) && ! Storage::disk('public')->exists($thumbPath)) {
                        try { Storage::disk('public')->copy($src, $thumbPath); } catch (\Throwable $e) { $thumbPath = $src; }
                    } elseif (! Storage::disk('public')->exists($thumbPath)) {
                        $thumbPath = $src;
                    }
                    $panorama->thumbnail = $thumbPath;
                } elseif (str_starts_with($src, '/')) {
                    // legacy /images -> keep as is, thumbnail sẽ là cùng path
                    $panorama->thumbnail = $src;
                }
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    public function hotspots(): HasMany
    {
        return $this->hasMany(Hotspot::class);
    }
}
