<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorProfile extends Model
{
    protected $fillable = [
        'user_id',
        'business_name',
        'pan_number',
        'business_address',
        'phone',
        'is_verified',
        'commission_rate',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
