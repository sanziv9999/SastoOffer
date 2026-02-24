<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorProfile extends Model
{
    protected $fillable = [
        'user_id',
        'business_name',
        'slug',
        'business_type_id',
        'pan_number',
        'verified_status',
        'verified_at',
        'verified_by_user_id',
        'commission_rate',
        'description',
        'website_url',
        'default_location_id',
    ];

    protected $casts = [
        'verified_at'     => 'datetime',
        'commission_rate' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function businessType(): BelongsTo
    {
        return $this->belongsTo(BusinessType::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function defaultLocation(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'default_location_id');
    }

    // Helper methods
    public function isVerified(): bool
    {
        return $this->verified_status === 'verified';
    }

    public function isPending(): bool
    {
        return $this->verified_status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->verified_status === 'rejected';
    }

    public function isSuspended(): bool
    {
        return $this->verified_status === 'suspended';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
