<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class VendorProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_name',
        'slug',
        'business_type',
        'primary_category_id',
        'verified_status',
        'verified_at',
        'verified_by_user_id',
        'description',
        'public_email',
        'public_phone',
        'website_url',
        'business_hours',
        'social_media',
        'default_address_id',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
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

    public function primaryCategory()
    {
        return $this->belongsTo(PrimaryCategory::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function defaultAddress()
    {
        return $this->belongsTo(Address::class, 'default_address_id');
    }

    /**
     * Polymorphic: images for this vendor (e.g. logo, cover, gallery).
     */
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
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