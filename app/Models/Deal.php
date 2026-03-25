<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vendor_id',
        'category_id',
        'title',
        'slug',
        'base_price',
        'short_description',
        'long_description',
        'highlights',
        'status',
        'total_inventory',
        'view_count',
    ];

    protected $casts = [
        'highlights'             => 'array',
        'view_count'             => 'integer',
        'base_price'             => 'decimal:2',
    ];

    // ─── Relationships ────────────────────────────────────────

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(VendorProfile::class, 'vendor_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subCategory(): BelongsTo
    {
        // Backwards-compatible alias for older code paths
        return $this->category();
    }

    /**
     * Many-to-many relationship with OfferType through pivot table
     */
    public function offerTypes(): BelongsToMany
    {
        return $this->belongsToMany(OfferType::class, 'deal_offer_type')
            ->using(DealOfferType::class)
            ->withPivot([
                'id',
                'original_price',
                'discount_percent',
                'discount_amount',
                'savings_amount',
                'savings_percent',
                'final_price',
                'currency_code',
                'params',
                'status',
                'starts_at',
                'ends_at',
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

    public function offerPivots(): HasMany
    {
        return $this->hasMany(DealOfferType::class, 'deal_id');
    }

    public function activeOfferPivots(): HasMany
    {
        return $this->offerPivots()->where('status', 'active');
    }

    /**
     * Polymorphic: multiple images per deal (e.g. cover, gallery).
     */
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getIsFeaturedAttribute(): bool
    {
        return $this->hasDisplayAs('featured');
    }

    public function getIsDealOfDayAttribute(): bool
    {
        return $this->hasDisplayAs('deals_of_the_day');
    }

    public function getIsBestSellerAttribute(): bool
    {
        return $this->hasDisplayAs('hot_sell');
    }

    public function getIsNewArrivalAttribute(): bool
    {
        return $this->hasDisplayAs('new_arrival');
    }

    /**
     * Featured image URL (prefers feature_photo, then first by sort_order).
     * Works on an already-loaded images relation to avoid extra queries.
     */
    public function featuredImageUrl(string $fallback = ''): string
    {
        $images = $this->relationLoaded('images') ? $this->images : $this->images()->get();
        $feature = $images->firstWhere('attribute_name', 'feature_photo') ?? $images->first();
        return $feature?->image_url ?? $fallback;
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
        return $this->status === 'active';
    }

    public function isRunning(): bool
    {
        return $this->isActive();
    }

    public function hasActiveOffers(): bool
    {
        return $this->activeOfferTypes()->exists();
    }

    protected function hasDisplayAs(string $displayAs): bool
    {
        return $this->activeOfferPivots()
            ->whereHas('displayTypes', fn ($q) => $q->where('name', $displayAs))
            ->exists();
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