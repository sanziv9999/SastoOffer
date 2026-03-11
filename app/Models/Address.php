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
        'user_id',
        'province', 'district', 'municipality', 'ward_no', 'tole', 'latitude', 'longitude',
        'is_default', 'label'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
