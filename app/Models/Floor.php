<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Floor extends Model
{
    protected $fillable = ['building_id', 'project_id', 'slug', 'name', 'short_label', 'description', 'plan_image', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $floor) {
            // building cho phép rỗng -> nếu rỗng mà có project thì tự vào building
            if (empty($floor->building_id) && ! empty($floor->project_id)) {
                $project = \App\Models\Project::find($floor->project_id);
                if ($project) {
                    $default = \App\Models\Building::firstOrCreate(
                        ['project_id' => $project->id, 'slug' => $project->slug . '-default'],
                        ['name' => 'Default Building', 'type' => 'group', 'is_active' => true, 'sort_order' => 999]
                    );
                    $floor->building_id = $default->id;
                    // giữ project_id để khi mở lại form vẫn hiện (không xóa)
                }
            }
            // nếu cả 2 rỗng và floor đã tồn tại thì giữ building cũ để không mất floor (tránh orphan như Ảnh 1)
            if (empty($floor->building_id) && empty($floor->project_id) && $floor->exists) {
                $orig = $floor->getOriginal('building_id');
                if (! empty($orig)) $floor->building_id = $orig;
                // đồng thời khôi phục project_id từ building gốc để form hiện lại
                if (empty($floor->project_id) && ! empty($orig)) {
                    $origBuilding = \App\Models\Building::find($orig);
                    if ($origBuilding) $floor->project_id = $origBuilding->project_id;
                }
            }
        });
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function panoramas(): HasMany
    {
        return $this->hasMany(Panorama::class);
    }

    public function videos(): MorphMany
    {
        return $this->morphMany(Video::class, 'videoable');
    }
}
