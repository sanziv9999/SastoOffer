<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['user_id', 'deal_offer_type_id', 'quantity'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function offerType()
    {
        return $this->belongsTo(\App\Models\DealOfferType::class, 'deal_offer_type_id');
    }
}
