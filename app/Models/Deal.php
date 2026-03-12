<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vendor_id',
        'business_sub_category_id',
        'title',
        'slug',
        'short_description',
        'long_description',
        'highlights',
        'status',
        'total_inventory',
        'min_per_customer',
        'max_per_customer',
        'starts_at',
        'ends_at',
        'voucher_valid_days',
        'view_count',
        'offer_validation_rules',
    ];

    protected $casts = [
        'highlights'             => 'array',
        'offer_validation_rules' => 'array',
        'starts_at'              => 'datetime',
        'ends_at'                => 'datetime',
        'view_count'             => 'integer',
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

    /**
     * Many-to-many relationship with OfferType through pivot table
     */
    public function offerTypes(): BelongsToMany
    {
        return $this->belongsToMany(OfferType::class, 'deal_offer_type')
            ->using(DealOfferType::class)
            ->withPivot([
                'original_price',
                'discount_percent',
                'discount_amount',
                'savings_amount',
                'savings_percent',
                'final_price',
                'currency_code',
                'params',
                'status',
            ])
            ->withTimestamps();
    }

    /**
     * Only active offer types (most common use-case)
     */
    public function activeOfferTypes(): BelongsToMany
    {
        return $this->offerTypes()->wherePivot('status', 'active');
    }

    /**
     * Polymorphic: multiple images per deal (e.g. cover, gallery).
     */
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function feature(): HasOne
    {
        return $this->hasOne(DealFeature::class);
    }

    public function getIsFeaturedAttribute(): bool
    {
        return (bool) ($this->feature?->is_featured ?? false);
    }

    public function getIsDealOfDayAttribute(): bool
    {
        return (bool) ($this->feature?->is_deal_of_day ?? false);
    }

    public function getIsBestSellerAttribute(): bool
    {
        return (bool) ($this->feature?->is_best_seller ?? false);
    }

    public function getIsNewArrivalAttribute(): bool
    {
        return (bool) ($this->feature?->is_new_arrival ?? false);
    }

    // ─── Accessors ───────────────────────────────────────────

    /**
     * Get the "primary" or "default" offer price for display/listing
     * You can choose logic: cheapest, highest discount, first active, etc.
     */
    public function getDisplayPriceAttribute(): ?float
    {
        $active = $this->activeOfferTypes()->first();

        return $active?->pivot?->final_price
            ?? $active?->pivot?->original_price
            ?? null;
    }

    /**
     * Get the highest savings percentage among active offers
     * Useful for "up to XX% off" badges
     */
    public function getMaxSavingsPercentAttribute(): float
    {
        if (!$this->activeOfferTypes()->exists()) {
            return 0;
        }

        return $this->activeOfferTypes()
            ->get()
            ->map(fn($ot) => (float) ($ot->pivot->savings_percent ?? 0))
            ->max() ?? 0;
    }

    /**
     * Get the best (lowest) final price among active offers
     */
    public function getBestPriceAttribute(): ?float
    {
        if (!$this->activeOfferTypes()->exists()) {
            return null;
        }

        return $this->activeOfferTypes()
            ->get()
            ->map(fn($ot) => (float) ($ot->pivot->final_price ?? $ot->pivot->original_price))
            ->min();
    }

    // ─── Helpers ──────────────────────────────────────────────

    public function isActive(): bool
    {
        $now = now();
        return $this->status === 'active'
            && ($this->starts_at === null || $now >= $this->starts_at)
            && ($this->ends_at === null || $now <= $this->ends_at);
    }

    public function isRunning(): bool
    {
        return $this->isActive();
    }

    public function hasActiveOffers(): bool
    {
        return $this->activeOfferTypes()->exists();
    }

    public function getCustomValidationRules(): array
    {
        return $this->offer_validation_rules ?? [];
    }

    /**
     * Recalculate all attached offer prices (e.g. after bulk param change)
     */
    public function recalculateAllOffers(): void
    {
        $this->offerTypes->each(function ($offerType) {
            $pivot = $offerType->pivot;
            if ($pivot instanceof DealOfferType) {
                $pivot->calculatePrices();
            }
        });
    }

    /**
     * Remaining inventory (placeholder — extend with purchases later)
     */
    public function remainingInventory(): ?int
    {
        return $this->total_inventory; // null = unlimited
    }
}