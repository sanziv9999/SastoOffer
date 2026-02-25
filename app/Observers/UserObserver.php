<?php

namespace App\Observers;

use App\Models\User;
use App\Models\CustomerProfile;
use App\Models\VendorProfile;

class UserObserver
{
    /**
     * Handle the user "created" event.
     */
    public function created(User $user): void
    {
        // Skip if user already has a profile (safety check)
        if ($user->customerProfile || $user->vendorProfile) {
            return;
        }

        // Check roles and create corresponding profile
        if ($user->hasRole('customer')) {
            CustomerProfile::create([
                'user_id' => $user->id,
                'full_name' => $user->name, // default from user
                // You can add more defaults if you have them from registration form
            ]);
        }

        if ($user->hasRole('vendor')) {
            VendorProfile::create([
                'user_id' => $user->id,
                'business_name' => $user->name . "'s Business", // placeholder — vendor will edit later
                'slug' => \Str::slug($user->name . '-' . $user->id),
                'commission_rate' => 10.00, // default
                'verified_status' => 'pending',
            ]);
        }

        // Optional: log or send welcome email
        // \Log::info("Profile created for user {$user->id} with role: " . $user->roles->pluck('name')->implode(', '));
    }

    /**
     * Optional: Handle role changes later (if user role is updated)
     */
    public function updated(User $user): void
    {
        // If role changed to vendor after registration
        if ($user->wasChanged('roles') && $user->hasRole('vendor') && !$user->vendorProfile) {
            VendorProfile::create([
                'user_id' => $user->id,
                'business_name' => $user->name . "'s Business",
                'slug' => \Str::slug($user->name . '-' . $user->id),
                'commission_rate' => 10.00,
                'verified_status' => 'pending',
            ]);
        }
    }
}