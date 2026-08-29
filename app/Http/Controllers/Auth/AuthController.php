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
    ) {}

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
        $remember = $request->remember ? true : false;

        if (! Auth::attempt($credentials, $remember)) {
            // Fall back to the customer portal guard so customers use the same login.
            if ($redirect = $this->attemptCustomerLogin($request, $credentials, $remember)) {
                return $redirect;
            }

            return back()
                ->withInput($request->only('email'))
                ->with('error', 'The provided credentials do not match our records.');
        }
        $request->session()->regenerate();

        /** @var User $user */
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

        $defaultRouteName = $user->defaultDashboardRouteName();
        $defaultRoute = route($defaultRouteName);

        if ($user->isEmployee()) {
            return redirect()->route($defaultRouteName);
        }

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

    public function resetForm(Request $request, string $token): View
    {
        return view('auth.reset', [
            'token' => $token,
            'email' => $request->string('email')->toString(),
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request): RedirectResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
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
        /** @var User|null $user */
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

    public function verifyNotice(Request $request): View|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route($user->defaultDashboardRouteName()));
        }

        return view('auth.verify-notice');
    }

    public function sendVerificationEmail(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route($user->defaultDashboardRouteName()));
        }

        $user->sendEmailVerificationNotification();

        return back()->with('status', 'A new verification link has been sent to your email address.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        Auth::guard('customer')->logout();

        $request->session()->forget('customer_api_token');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Authenticate a portal customer through the same /login form (no shop code).
     * Returns a redirect on success, or null so the caller can fall through to the
     * standard "invalid credentials" response.
     */
    private function attemptCustomerLogin(Request $request, array $credentials, bool $remember): ?RedirectResponse
    {
        $authenticated = Auth::guard('customer')->attempt(
            $credentials + ['portal_enabled' => true],
            $remember
        );

        if (! $authenticated) {
            return null;
        }

        $request->session()->regenerate();

        return redirect()->intended(route('customer.dashboard'));
    }

    private function resolveLoginBlockMessage(User $user): ?string
    {
        if ($user->isEmployee() && empty($user->tenant_id)) {
            return 'Employee accounts must belong to a tenant workspace.';
        }

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
