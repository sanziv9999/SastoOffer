<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'phone',
        'delivery_address',
        'preferred_payment_method',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
