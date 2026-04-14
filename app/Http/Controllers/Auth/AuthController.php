<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\RegisterTenantShopAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterShopRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        private readonly RegisterTenantShopAction $registerTenantShopAction,
    ) {
    }

    public function register(): View
    {
        return view('auth.register');
    }

    public function store(RegisterShopRequest $request): RedirectResponse
    {
        $user = $this->registerTenantShopAction->execute($request->validated());

        event(new Registered($user));

        return redirect()
            ->route('login')
            ->with('success', 'Registration submitted. Verify your email, then wait for super admin approval.');
    }

    public function login(): View
    {
        return view('auth.login');
    }

    public function loginSubmit(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->safe()->only(['email', 'password']);
        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'The provided credentials do not match our records.');
        }

        $request->session()->regenerate();

        /** @var \App\Models\User $user */
        $user = $request->user();

        if (! $user->hasVerifiedEmail()) {
            return $this->logoutBlockedUser($request, 'Verify your email address before signing in.');
        }

        if ($message = $this->resolveLoginBlockMessage($user)) {
            return $this->logoutBlockedUser($request, $message);
        }

        $user->forceFill([
            'failed_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        $defaultRoute = $user->isSuperAdmin()
            ? route('admin.dashboard')
            : route('tenant.dashboard');

        return redirect()->intended($defaultRoute);
    }

    public function forgot(): View
    {
        return view('auth.forgot');
    }

    public function sendResetLink(ForgotPasswordRequest $request): RedirectResponse
    {
        $status = Password::sendResetLink($request->validated());

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', __($status))
            : back()->withInput($request->only('email'))->with('error', __($status));
    }

    public function resetForm(string $token): View
    {
        return view('auth.reset', ['token' => $token]);
    }

    public function resetPassword(ResetPasswordRequest $request): RedirectResponse
    {
        $status = Password::reset(
            $request->validated(),
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

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return redirect()
            ->route('login')
            ->with('success', 'Email verified successfully. Your account now awaits super admin approval.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function resolveLoginBlockMessage(User $user): ?string
    {
        if ($user->tenant_id) {
            $tenant = $user->tenant()->first();

            if (! $tenant) {
                return 'Tenant account could not be found.';
            }

            if (! $tenant->status->allowsLogin()) {
                return $tenant->status->loginBlockedMessage();
            }
        }

        if (! $user->is_active) {
            return 'Your user account is inactive.';
        }

        return null;
    }

    private function logoutBlockedUser(Request $request, string $message): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('warning', $message);
    }
}
