<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\CustomerProfile;
use App\Models\VendorProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function showLoginForm(): Response
    {
        if (Auth::check()) {
            return $this->redirectByRole();
        }
        return Inertia::render('LoginPage');
    }

    public function login(LoginRequest $request): Response
    {
        $request->authenticate();
        $request->session()->regenerate();

        return $this->redirectByRole();
    }

    public function showRegisterForm(): Response
    {
        return Inertia::render('RegisterPage');
    }

    public function register(RegisterRequest $request): Response
    {
        $user = User::create([
            'name'     => $request->validated('name'),
            'email'    => $request->validated('email'),
            // 'phone'    => $request->validated('phone'),
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

    public function logout(Request $request): Response
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Inertia::render('LoginPage');
    }

    protected function redirectByRole(): Response
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return Inertia::render('AdminDashboard');
        }
        if ($user->hasRole('vendor')) {
            $profile = $user->vendorProfile ?? null;
            if ($profile) {
                return Inertia::render('VendorProfile');
            }
            return Inertia::render('VendorDashboard');
        }
        if ($user->hasRole('customer')) {
            $profile = $user->customerProfile ?? null;
            if ($profile) {
                return Inertia::render('UserDashboard');
            }
            return Inertia::render('UserDashboard');
        }

        return Inertia::render('HomePage');
    }
}
