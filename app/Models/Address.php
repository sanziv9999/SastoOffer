<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    /** @use HasFactory<\Database\Factories\AddressFactory> */
    use HasFactory;

    protected $fillable = [
        'address_line', 'city', 'state_province',
        'postal_code', 'country_code', 'latitude', 'longitude',
        'timezone', 'is_default', 'label',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
