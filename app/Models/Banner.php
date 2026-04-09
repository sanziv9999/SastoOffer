<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Banner extends Model
{
    protected $fillable = [
        'title',
        'text',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Polymorphic images (use attribute_name e.g. `image` for the slide artwork).
     */
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    /**
     * Banners marked for the landing page carousel / hero.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Default order for landing display (lower sort_order first).
     */
    public function scopeOrderedForLanding(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
