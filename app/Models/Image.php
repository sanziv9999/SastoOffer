<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Image extends Model
{
    protected $fillable = [
        'imageable_type',
        'imageable_id',
        'attribute_name',
        'image_url',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * The owning model (User, VendorProfile, CustomerProfile, BusinessType, BusinessSubCategory, Deal).
     */
    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope by attribute name (e.g. cover, logo, gallery).
     */
    public function scopeForAttribute($query, string $name)
    {
        return $query->where('attribute_name', $name);
    }
}
