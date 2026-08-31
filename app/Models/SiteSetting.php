<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SiteSetting extends Model
{
    protected $fillable = [
        'company_name', 'logo', 'phone', 'email', 'address',
        'website', 'description', 'facebook', 'copyright',
    ];

    protected $appends = ['logo_url'];

    public function getLogoUrlAttribute(): ?string
    {
        if (blank($this->logo)) return null;
        if (str_starts_with($this->logo, 'http') || str_starts_with($this->logo, '/storage') || str_starts_with($this->logo, '/images')) {
            return $this->logo;
        }
        if (Storage::disk('public')->exists($this->logo)) {
            return Storage::disk('public')->url($this->logo);
        }
        return $this->logo;
    }

    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1], [
            'company_name' => null,
            'email' => null,
        ]);
    }
}
