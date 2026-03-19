<?php

namespace App\Http\Middleware;

use App\Models\VendorProfile;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVendorIsApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole('vendor')) {
            return $next($request);
        }

        $vendorProfile = VendorProfile::query()
            ->with('defaultAddress')
            ->where('user_id', $user->id)
            ->first();

        $menusUnlocked = (bool) ($vendorProfile?->hasUnlockedVendorMenus() ?? false);

        if ($menusUnlocked) {
            return $next($request);
        }

        return redirect()
            ->route('vendor.settings')
            ->with('error', 'Complete business details and wait for admin verification to unlock vendor menus.');
    }
}
