<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'icon_key',
        'description',
        'image_url',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getIconAttribute(): string
    {
        return (string) ($this->icon_key ?: 'gift');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Backwards-compatible alias for older code paths that used
     * BusinessSubCategory->primaryCategory().
     */
    public function primaryCategory()
    {
        return $this->parent();
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')
            ->orderBy('display_order');
    }

    public function banners()
    {
        return $this->hasMany(Banner::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
