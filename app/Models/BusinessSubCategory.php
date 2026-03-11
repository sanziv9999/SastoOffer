<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class BusinessSubCategory extends Model
{
    protected $fillable = [
        'primary_category_id',
        'name',
        'slug',
        'description',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────

    /**
     * The primary category this sub-category belongs to
     */
    public function primaryCategory()
    {
        return $this->belongsTo(PrimaryCategory::class);
    }

    // Optional: if later you have products/offers that belong to sub-category
    public function offers()
    {
        return $this->hasMany(Offer::class); // or Product::class, etc.
    }

    /**
     * Polymorphic: images for this sub-category (e.g. icon, banner).
     */
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    // ─── Scopes ───────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
