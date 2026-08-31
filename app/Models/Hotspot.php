<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hotspot extends Model
{
    protected $fillable = ['panorama_id', 'target_panorama_id', 'tooltip', 'yaw', 'pitch', 'sort_order'];

    protected $casts = [
        'yaw' => 'decimal:2',
        'pitch' => 'decimal:2',
    ];

    public function panorama(): BelongsTo
    {
        return $this->belongsTo(Panorama::class, 'panorama_id');
    }

    public function targetPanorama(): BelongsTo
    {
        return $this->belongsTo(Panorama::class, 'target_panorama_id');
    }
}
