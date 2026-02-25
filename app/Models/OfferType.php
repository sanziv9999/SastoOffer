<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OfferType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'slug',
        'description',
        'calculation_rule',
        'required_params',
        'default_values',
        'is_active',
    ];

    protected $casts = [
        'calculation_rule'  => 'array',
        'required_params'   => 'array',
        'default_values'    => 'array',
        'is_active'         => 'boolean',
    ];

    public function deals(): BelongsToMany
    {
        return $this->belongsToMany(Deal::class, 'deal_offer_type')
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
}