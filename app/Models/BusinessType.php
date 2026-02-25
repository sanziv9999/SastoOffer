<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
