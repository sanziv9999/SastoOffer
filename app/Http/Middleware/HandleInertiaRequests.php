<?php

namespace App\Http\Middleware;

use App\Models\VendorProfile;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $authUser = $request->user();
        $role = $authUser?->getRoleNames()->first() ?? 'customer';
        $vendorAccess = null;
        $vendorMetrics = null;

        if ($authUser && $role === 'vendor') {
            $vendorProfile = VendorProfile::query()
                ->with('defaultAddress')
                ->where('user_id', $authUser->id)
                ->first();

            $isComplete = (bool) ($vendorProfile?->hasCompletedBusinessDetails() ?? false);
            $isVerified = (bool) ($vendorProfile?->isVerified() ?? false);

            $vendorAccess = [
                'has_profile' => (bool) $vendorProfile,
                'is_complete' => $isComplete,
                'is_verified' => $isVerified,
                'is_unlocked' => $isComplete && $isVerified,
                'verified_status' => $vendorProfile?->verified_status,
            ];

            if ($vendorProfile) {
                $vendorMetrics = [
                    'open_orders' => \App\Models\Order::where('vendor_id', $vendorProfile->id)
                        ->whereIn('status', ['pending', 'paid'])
                        ->count(),
                    'total_orders' => \App\Models\Order::where('vendor_id', $vendorProfile->id)->count(),
                ];
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $authUser ? [
                    'id' => $authUser->id,
                    'name' => $authUser->name,
                    'email' => $authUser->email,
                    'role' => $role,
                ] : null,
                'vendor_access' => $vendorAccess,
                'vendor_metrics' => $vendorMetrics,
            ],
            'categories' => \App\Models\Category::where('is_active', true)
                ->orderBy('display_order')
                ->orderBy('name')
                ->get(['id', 'parent_id', 'name', 'slug', 'display_order']),
            'offerTypes' => \App\Models\OfferType::where('is_active', true)->get(['id', 'name', 'display_name']),
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ];
    }
}
