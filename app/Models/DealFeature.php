<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealFeature extends Model
{
    protected $fillable = [
        'deal_id',
        'is_featured',
        'is_deal_of_day',
        'is_best_seller',
        'is_new_arrival',
        'rank',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_deal_of_day' => 'boolean',
        'is_best_seller' => 'boolean',
        'is_new_arrival' => 'boolean',
        'rank' => 'integer',
    ];

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }
}

