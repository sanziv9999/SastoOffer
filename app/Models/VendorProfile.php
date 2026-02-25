<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorProfile extends Model
{
    use HasFactory;

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
        'verified_at' => 'datetime',
        'commission_rate' => 'decimal:2',
    ];

    // ─── Relationships ────────────────────────────────────────

    /**
     * The user this vendor profile belongs to
     * → ONLY ONE copy of this method
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function businessType()
    {
        return $this->belongsTo(BusinessType::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function defaultLocation()
    {
        return $this->belongsTo(Location::class, 'default_location_id'); // or Address
    }

    // ─── Helpers ──────────────────────────────────────────────

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
}