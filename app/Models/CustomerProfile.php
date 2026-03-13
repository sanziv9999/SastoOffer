<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CustomerProfile extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'full_name',
        'date_of_birth',
        'gender',
        'phone',
        'default_address_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_of_birth'     => 'date:Y-m-d',     // or 'date' if you want Carbon instance
        'default_address_id' => 'integer',
    ];

    /**
     * Extra attributes that should be appended when serializing.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'username',
        'email',
        'age',
        'gender_display',
        'profile_pic_url',
    ];

    /**
     * The attributes that should be hidden for arrays/JSON.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'user_id',          // usually not needed in API responses
    ];

    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the default address for this customer.
     */
    public function defaultAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'default_address_id');
    }

    /**
     * Polymorphic: images for this customer (e.g. profile_pic, gallery).
     */
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    // ────────────────────────────────────────────────
    // Optional: Accessors / Helpers
    // ────────────────────────────────────────────────

    /**
     * Get the profile picture URL (with fallback).
     */
    public function getProfilePicUrlAttribute(): ?string
    {
        $image = $this->relationLoaded('images')
            ? $this->images->firstWhere('attribute_name', 'profile_pic')
            : $this->images()->where('attribute_name', 'profile_pic')->first();

        if ($image && $image->image_url) {
            return $image->image_url;
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->full_name ?? 'User') . '&size=128';
    }

    /**
     * Check if the profile has a valid default address.
     */
    public function hasDefaultAddress(): bool
    {
        return !is_null($this->default_address_id) && $this->defaultAddress !== null;
    }

    /**
     * Get age from date of birth (if exists).
     */
    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth?->age;
    }

    /**
     * Expose username from related user model.
     */
    public function getUsernameAttribute(): ?string
    {
        return $this->user?->name;
    }

    /**
     * Expose email from related user model.
     */
    public function getEmailAttribute(): ?string
    {
        return $this->user?->email;
    }

    /**
     * Format gender for display.
     */
    public function getGenderDisplayAttribute(): string
    {
        return match (strtolower($this->gender ?? '')) {
            'male'   => 'Male',
            'female' => 'Female',
            'other'  => 'Other',
            default  => 'Prefer not to say',
        };
    }
}