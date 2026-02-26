<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\CustomerProfile;
use App\Models\VendorProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        return $this->redirectByRole();
    }

    public function showRegisterForm(): View
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'name'     => $request->validated('name'),
            'email'    => $request->validated('email'),
            'phone'    => $request->validated('phone'),
            'password' => Hash::make($request->validated('password')),
            'status'   => 'active',
        ]);

        $user->assignRole($request->validated('role'));

        if ($user->hasRole('vendor') && ! $user->vendorProfile) {
            VendorProfile::create([
                'user_id'         => $user->id,
                'business_name'   => $user->name . "'s Business",
                'slug'            => Str::slug($user->name . '-' . $user->id),
                'commission_rate' => 10.00,
                'verified_status' => 'pending',
            ]);
        }
        if ($user->hasRole('customer') && ! $user->customerProfile) {
            CustomerProfile::create([
                'user_id'   => $user->id,
                'full_name' => $user->name,
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return $this->redirectByRole();
    }

    public function logout(\Illuminate\Http\Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    protected function redirectByRole(): RedirectResponse
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return redirect()->intended(route('deals.index'));
        }
        if ($user->hasRole('vendor')) {
            $profile = $user->vendorProfile ?? null;
            if ($profile) {
                return redirect()->intended(route('vendor-profiles.show', $profile));
            }
            return redirect()->intended(route('vendor-profiles.index'));
        }
        if ($user->hasRole('customer')) {
            $profile = $user->customerProfile ?? null;
            if ($profile) {
                return redirect()->intended(route('customer-profiles.show', $profile));
            }
            return redirect()->intended(route('customer-profiles.index'));
        }

        return redirect()->intended(route('deals.index'));
    }
}
