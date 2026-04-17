<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DisplayType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function dealOfferTypes(): BelongsToMany
    {
        return $this->belongsToMany(DealOfferType::class, 'deal_offer_display', 'display_as', 'deal_offer_type_id')
            ->withTimestamps();
    }

    /** Required for featured placements; created automatically if missing. */
    public static function featured(): self
    {
        return static::firstOrCreate(['name' => 'featured']);
    }
}

