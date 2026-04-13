<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function register(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email', 'unique:tenants,email'],
            'password' => ['required', 'string', 'confirmed', 'min:8'],
            'phone' => ['nullable', 'string', 'max:30'],
            'shop_name' => ['required', 'string', 'max:255'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'business_type' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'terms' => ['accepted'],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $tenant = Tenant::create([
                'id' => (string) Str::uuid(),
                'shop_name' => $validated['shop_name'],
                'business_type' => $validated['business_type'] ?? null,
                'owner_name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'website_url' => $validated['website_url'] ?? null,
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'] ?? null,
                'state' => $validated['state'] ?? null,
                'country' => $validated['country'] ?? null,
                'status' => 'pending',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'tenant_id' => $tenant->id,
                'role' => User::TENANT_ADMIN,
                'phone' => $validated['phone'] ?? null,
                'is_active' => false,
            ]);

            if (method_exists($user, 'assignRole')) {
                $user->assignRole(User::TENANT_ADMIN);
            }

            return $user;
        });

        event(new Registered($user));

        return redirect()
            ->route('login')
            ->with('success', 'Registration submitted. Verify your email before signing in.');
    }

    public function login(): View
    {
        return view('auth.login');
    }

    public function loginSubmit(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'The provided credentials do not match our records.');
        }

        $request->session()->regenerate();

        /** @var \App\Models\User $user */
        $user = $request->user();

        if (is_null($user->email_verified_at)) {
            Auth::logout();

            return redirect()
                ->route('login')
                ->with('warning', 'Verify your email address before signing in.');
        }

        return redirect()->intended('/');
    }

    public function forgot(): View
    {
        return view('auth.forgot');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink($validated);

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', __($status))
            : back()->withInput($request->only('email'))->with('error', __($status));
    }

    public function resetForm(string $token): View
    {
        return view('auth.reset', ['token' => $token]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'confirmed', 'min:8'],
        ]);

        $status = Password::reset(
            $validated,
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', __($status))
            : back()->withInput($request->only('email'))->with('error', __($status));
    }

    public function verifyEmail(Request $request, int|string $id, string $hash): RedirectResponse
    {
        /** @var \App\Models\User|null $user */
        $user = User::find($id);

        if (! $user || ! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            abort(403);
        }

        if (is_null($user->email_verified_at)) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        return redirect()
            ->route('login')
            ->with('success', 'Email verified successfully. Your account is pending admin approval.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
