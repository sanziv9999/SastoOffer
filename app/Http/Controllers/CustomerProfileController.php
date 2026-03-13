<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCustomerProfileRequest;
use App\Models\CustomerProfile;
use Illuminate\Http\Request;

class CustomerProfileController extends Controller
{
    public function update(UpdateCustomerProfileRequest $request)
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        /** @var CustomerProfile $profile */
        $profile = $user->customerProfile()->firstOrCreate(
            ['user_id' => $user->id],
            ['full_name' => $user->name]
        );

        $data = $request->validated();
        $profile->fill($data);
        $profile->save();

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updateAvatar(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        /** @var CustomerProfile $profile */
        $profile = $user->customerProfile()->firstOrCreate(
            ['user_id' => $user->id],
            ['full_name' => $user->name]
        );

        $validated = $request->validate([
            'profile_pic' => ['required', 'image', 'max:5120'],
        ]);

        $file = $request->file('profile_pic');
        if (! ($file && $file->isValid())) {
            return back()->withErrors(['profile_pic' => 'Invalid profile picture upload.']);
        }

        $path = $file->store('customer_profiles/profile_pics', 'public');

        // Remove previous profile_pic image entries
        $profile->images()->where('attribute_name', 'profile_pic')->delete();

        $profile->images()->create([
            'attribute_name' => 'profile_pic',
            'image_url'      => '/storage/' . $path,
        ]);

        return back()->with('success', 'Profile picture updated successfully.');
    }
}

