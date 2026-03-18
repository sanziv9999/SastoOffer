<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealOfferDisplay extends Model
{
    use HasFactory;

    protected $table = 'deal_offer_display';

    protected $fillable = [
        'deal_offer_type_id',
        'display_as',
    ];

    public function dealOfferType(): BelongsTo
    {
        return $this->belongsTo(DealOfferType::class, 'deal_offer_type_id');
    }

    public function displayType(): BelongsTo
    {
        return $this->belongsTo(DisplayType::class, 'display_as');
    }
}

