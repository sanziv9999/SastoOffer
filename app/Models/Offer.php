<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable=[
        'vendor_id',
        'business_sub_category_id',
        'title',
        'slug',
        'short_description',
        'long_description',
        'highlights',
        'offer_type_id',
        'status',
        'original_price',
        'offer_price',
        'discount_percent',
        'currency_code',
        'total_inventory',
        'min_per_customer',
        'max_per_customer',
        'starts_at',
        'ends_at',
        'voucher_valid_days',
        'is_featured',
        'view_count',
        'offer_validation_rules',
    ];


    protected $casts = [
        'highlights'              => 'array',
        'offer_validation_rules'  => 'array',
        'starts_at'               => 'datetime',
        'ends_at'                 => 'datetime',
        'is_featured'             => 'boolean',
        'view_count'              => 'integer',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(VendorProfile::class, 'vendor_id');
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(BusinessSubCategory::class, 'business_sub_category_id');
    }

    public function offerType(): BelongsTo
    {
        return $this->belongsTo(OfferType::class);
    }

    // ─── Helpers ──────────────────────────────────────────────

    /**
     * Check if the offer is currently active
     */
    public function isActive(): bool
    {
        $now = now();
        return $this->status === 'active'
            && ($this->starts_at === null || $now >= $this->starts_at)
            && ($this->ends_at === null || $now <= $this->ends_at);
    }

    /**
     * Get custom validation rules as array
     */
    public function getCustomValidationRules(): array
    {
        return $this->offer_validation_rules ?? [];
    }

    /**
     * Calculate effective discount percent (if not stored)
     */
    public function getEffectiveDiscountPercentAttribute(): float
    {
        if ($this->original_price > 0 && $this->offer_price > 0) {
            return (($this->original_price - $this->offer_price) / $this->original_price) * 100;
        }
        return $this->discount_percent ?? 0;
    }
}
