<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Video extends Model
{
    protected $fillable = ['videoable_type', 'videoable_id', 'title', 'label', 'description', 'thumbnail', 'video_url', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function videoable(): MorphTo
    {
        return $this->morphTo();
    }
}
