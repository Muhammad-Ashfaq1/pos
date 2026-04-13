<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    // ---------------- REGISTER ----------------

    public function register()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
              'email'    => 'required|email|unique:users,email',
            'phone'    => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',

            'shop_name'     => 'required|string|max:255',
            'business_type' => 'nullable|string|max:255',
            'website_url'   => 'nullable|url|max:255',
            'address'       => 'nullable|string|max:255',
            'city'          => 'nullable|string|max:100',
            'state'         => 'nullable|string|max:100',
            'country'       => 'nullable|string|max:100',
        ]);

        DB::transaction(function () use ($validated) {

            // CREATE TENANT
            $tenant = Tenant::create([
                'id' => (string) Str::uuid(),

                'shop_name' => $validated['shop_name'],
                'owner_name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'business_type' => $validated['business_type'] ?? null,
                'website_url' => $validated['website_url'] ?? null,
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'] ?? null,
                'state' => $validated['state'] ?? null,
                'country' => $validated['country'] ?? null,
                'status' => 'pending',
                'onboarding_status' => 'not_started',
            ]);

            // CREATE USER (Tenant Admin)
            $user = User::create([
                'tenant_id' => $tenant->id,
                'name'      => $validated['name'],
                'email'     => $validated['email'],
                'phone'     => $validated['phone'] ?? null,
                'password'  => Hash::make($validated['password']),
                'is_active' => false,
            ]);

            
            $user->assignRole(User::TENANT_ADMIN);

            $user->sendEmailVerificationNotification();
        });

        return redirect()->route('login')
            ->with('success', 'Signup successful. Wait for admin approval.');
    }

    // ---------------- LOGIN (WITH LOCKOUT) ----------------
    public function login()
{
    return view('auth.login');
}


public function loginSubmit(Request $request)
{
    $request->validate([
        'email'    => 'required|email',
        'password' => 'required|string',
    ]);

    $email = strtolower($request->email);
    $ip = $request->ip();

    // RateLimiter key
    $key = 'login:' . $email . ':' . $ip;

    // Too many attempts (RateLimiter)
    if (RateLimiter::tooManyAttempts($key, 5)) {
        return back()->with('error', ' Too many attempts. Try again later.');
    }

    $user = User::where('email', $email)->first();

    
    if ($user && $user->locked_until && now()->lt($user->locked_until)) {
        return back()->with('error', 'Account locked until ' . $user->locked_until->diffForHumans());
    }

    // WRONG LOGIN (email OR password)
    if (!$user || !Hash::check($request->password, $user->password)) {

        RateLimiter::hit($key, 60);

        if ($user) {

            $user->increment('failed_attempts');
            $user->refresh();

            // LOCK AFTER 5 FAILS
            if ($user->failed_attempts >= 5) {

                $user->update([
                    'locked_until' => now()->addMinutes(10),
                    'failed_attempts' => 0,
                ]);

                return back()->with('error', 'Account locked for 10 minutes.');
            }
        }

        return back()->with('error', ' Invalid credentials.');
    }

    // SUCCESS LOGIN → reset security
    RateLimiter::clear($key);

    $user->update([
        'failed_attempts' => 0,
        'locked_until' => null,
        'last_login_at' => now(),
        'last_login_ip' => $ip,
    ]);

    Auth::login($user, $request->boolean('remember'));

    // EMAIL VERIFY CHECK
    if (!$user->hasVerifiedEmail()) {
        Auth::logout();
        return back()->with('error', 'Please verify your email first.');
    }

    //  TENANT CHECK
    $tenant = Tenant::find($user->tenant_id);

    if (!$tenant || $tenant->status !== 'approved') {
        Auth::logout();
        return back()->with('error', 'Tenant not approved.');
    }

    //  ACTIVE CHECK
    if (!$user->is_active) {
        Auth::logout();
        return back()->with('error', 'Account not active.');
    }

    app()->instance('currentTenant', $tenant);

    return redirect()->route('home');
}
    // ---------------- FORGOT PASSWORD ----------------

    public function forgot()
    {
        return view('auth.forgot');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', 'Reset link sent.')
            : back()->with('error', 'Email not found.');
    }

    public function resetForm(string $token)
    {
        return view('auth.reset', ['token' => $token]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->update([
                    'password' => Hash::make($password),
                ]);
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Password reset successful.')
            : back()->with('error', 'Invalid reset link.');
    }

    // ---------------- EMAIL VERIFY ----------------

    public function verifyEmail(string $id, string $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            abort(403);
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return redirect()->route('login')
            ->with('success', 'Email verified. Wait for approval.');
    }

    // ---------------- APPROVE TENANT ----------------

    public function approveTenant($id)
    {
        if (!auth()->user()->isSuperAdmin()) {
    abort(403);
}
        $tenant = Tenant::findOrFail($id);

        $tenant->update([
            'status' => 'approved',
            'approved_at' => now(),
            'onboarding_status' => 'in_progress',
        ]);

        User::where('tenant_id', $tenant->id)
            ->update(['is_active' => true]);

        return back()->with('success', 'Tenant approved successfully.');
    }

    // ---------------- LOGOUT ----------------

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}