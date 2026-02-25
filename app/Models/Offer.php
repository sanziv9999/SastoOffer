<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [
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
        'discount_amount',
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
        'original_price'          => 'decimal:2',
        'offer_price'             => 'decimal:2',
        'discount_percent'        => 'decimal:2',
        'discount_amount'         => 'decimal:2',
    ];

    protected $appends = [
        'effective_discount_percent',
        'savings_amount',
        'savings_percent',
    ];

    // ─── Relationships ────────────────────────────────────────

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
        return $this->belongsTo(OfferType::class, 'offer_type_id');
    }

    // ─── Accessors (calculated on-the-fly) ──────────────────────────────

    /**
     * Get effective discount percent (uses stored value or recalculates from prices)
     */
    public function getEffectiveDiscountPercentAttribute(): float
    {
        if ($this->original_price > 0 && $this->offer_price > 0) {
            return round((($this->original_price - $this->offer_price) / $this->original_price) * 100, 2);
        }

        return $this->discount_percent ?? 0;
    }

    /**
     * Savings in absolute amount (Rs)
     */
    public function getSavingsAmountAttribute(): float
    {
        return round($this->original_price - $this->offer_price, 2);
    }

    /**
     * Savings as percentage
     */
    public function getSavingsPercentAttribute(): float
    {
        if ($this->original_price > 0) {
            return round(($this->getSavingsAmountAttribute() / $this->original_price) * 100, 2);
        }

        return 0;
    }

    // ─── Helpers ──────────────────────────────────────────────

    public function isActive(): bool
    {
        $now = now();
        return $this->status === 'active'
            && ($this->starts_at === null || $now >= $this->starts_at)
            && ($this->ends_at === null || $now <= $this->ends_at);
    }

    public function getCustomValidationRules(): array
    {
        return $this->offer_validation_rules ?? [];
    }

    /**
     * Check if this offer is currently running (active + within date range)
     */
    public function isRunning(): bool
    {
        return $this->isActive() && $this->status === 'active';
    }

    /**
     * Get remaining inventory (handles unlimited case)
     */
    public function remainingInventory(): ?int
    {
        return $this->total_inventory; // can be extended with sold count later
    }
}