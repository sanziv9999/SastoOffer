<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferType extends Model
{
    /** @use HasFactory<\Database\Factories\OfferTypeFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ────────────────────────────────────────────────
    // Helpers / Accessors
    // ────────────────────────────────────────────────

    /**
     * Get the friendly display name (fallback to name if empty)
     */
    public function getDisplayNameAttribute($value): string
    {
        return $value ?: ucfirst(str_replace('_', ' ', $this->name));
    }

    
}


    
