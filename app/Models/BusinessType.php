<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class BusinessType extends Model
{
    /** @use HasFactory<\Database\Factories\BusinessTypeFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'display_order',
        'is_active',
    ];

    public function vendor()
    {
        return $this->hasMany(VendorProfile::class);
    }

    /**
     * Get all sub-categories under this business type
     */
    public function subCategories()
    {
        return $this->hasMany(BusinessSubCategory::class);
    }

    /**
     * Polymorphic: images for this business type (e.g. icon, banner).
     */
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}
