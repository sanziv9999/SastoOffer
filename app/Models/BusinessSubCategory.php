<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class BusinessSubCategory extends Model
{
    protected $fillable = [
        'business_type_id',
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
     * The Business Type this sub-category belongs to
     */
    public function businessType()
    {
        return $this->belongsTo(BusinessType::class);
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
