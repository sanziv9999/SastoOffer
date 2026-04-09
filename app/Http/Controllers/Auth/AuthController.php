<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\CustomerProfile;
use App\Models\VendorProfile;
use App\Models\User;
use App\Services\ActivityMailer;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    /** @return Response|RedirectResponse */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectByRole();
        }
        return Inertia::render('LoginPage');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        return $this->redirectByRole();
    }

    public function showRegisterForm(): Response
    {
        return Inertia::render('RegisterPage');
    }

    public function register(RegisterRequest $request, ActivityMailer $activityMailer): RedirectResponse
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

        try {
            $activityMailer->sendRegistrationWelcome($user);
        } catch (\Throwable $e) {
            Log::warning('Registration welcome mail skipped', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $this->redirectByRole();
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login', [], 303);
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('success', __($status));
        }

        return back()->withErrors(['email' => __($status)]);
    }

    public function showResetPasswordForm(Request $request, string $token): Response
    {
        return Inertia::render('ResetPasswordPage', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', __($status));
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }

    protected function redirectByRole(): RedirectResponse
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }
        // Treat any legacy "super_admin" role as admin.
        if ($user->hasRole('super_admin')) {
            return redirect()->route('admin.dashboard');
        }
        if ($user->hasRole('vendor')) {
            return redirect()->route('vendor.dashboard');
        }
        if ($user->hasRole('customer')) {
            return redirect()->route('dashboard');
        }

        return redirect()->route('home');
    }
}
