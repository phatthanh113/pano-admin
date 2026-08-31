<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = ['slug', 'name', 'description', 'thumbnail', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function buildings(): HasMany
    {
        return $this->hasMany(Building::class);
    }

    public function panoramas(): HasMany
    {
        return $this->hasMany(Panorama::class);
    }
}
