<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeaturedDealRank extends Model
{
    protected $table = 'featured_deal_ranks';

    protected $fillable = [
        'deal_id',
        'rank',
    ];

    protected $casts = [
        'rank' => 'integer',
    ];

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }
}

