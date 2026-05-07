# Authentication & Onboarding

This document walks the full user journey from arriving at the homepage to landing on a dashboard, covering shop registration, email verification, super-admin approval, login, password reset, impersonation, and logout.

All routes for guest/auth flows live in [routes/auth.php](../routes/auth.php) and the controller is [`AuthController`](../app/Http/Controllers/Auth/AuthController.php).

## Actor matrix

| Actor | How they're created | Default landing |
|-------|---------------------|-----------------|
| **Super admin** | [`SuperAdminSeeder`](../database/seeders/SuperAdminSeeder.php) at install time. `tenant_id = null`. | `admin.dashboard` |
| **Tenant admin** | Self-registers via `/register`. Created with `is_active = false`, status `pending` until approved. | `tenant.dashboard` |
| **Employee** (manager / cashier / technician / inventory clerk) | Created by a tenant admin from the roles & permissions screen. Always has a `tenant_id`. | `employee.dashboard` |

The route a logged-in user is sent to is determined by [`User::defaultDashboardRouteName()`](../app/Models/User.php#L110-L118):

```php
return match (true) {
    $this->isSuperAdmin() => 'admin.dashboard',
    $this->isEmployee()   => 'employee.dashboard',
    !empty($this->tenant_id) => 'tenant.dashboard',
    default => 'admin.dashboard',
};
```

## Public landing

[routes/web.php](../routes/web.php) is dead simple:

```php
Route::get('/', function () {
    if (!Auth::check()) return view('public.home');
    return redirect()->route(auth()->user()->defaultDashboardRouteName());
});
```

Unauthenticated visitors see [resources/views/public/home.blade.php](../resources/views/public/home.blade.php) which links to `/login` and `/register`. Anyone already logged in is redirected to their dashboard.

## Shop registration (signup)

### Route

```
GET  /register                      → AuthController@register     (form)
POST /register                      → AuthController@store        (submit)
```

Both are guarded by the `guest` middleware so logged-in users can't re-register.

### Validation

[`RegisterShopRequest`](../app/Http/Requests/Auth/RegisterShopRequest.php) validates the multi-section form covering owner identity, shop details, and address fields. The `password` rule uses Laravel's password rules (`Password::defaults()`).

### Action

[`AuthController@store`](../app/Http/Controllers/Auth/AuthController.php#L32-L41) hands off to [`RegisterTenantShopAction::execute()`](../app/Actions/Auth/RegisterTenantShopAction.php), which runs in a single DB transaction:

```
1. INSERT tenants
   - status = 'pending'
   - onboarding_status = 'not_started'
   - slug = str_slug(shop_name) + '-' + 6 random chars
2. INSERT users
   - role = 'tenant_admin'
   - is_active = false      ← cannot log in yet
   - tenant_id = new_tenant.id
3. assignPrimaryRole('tenant_admin', tenant.id)
   - sets PermissionTeam to tenant.id, syncs Spatie role
   - mirrors role into users.role column
```

Once committed, the controller fires `Illuminate\Auth\Events\Registered`, which triggers [`User::sendEmailVerificationNotification()`](../app/Models/User.php#L125-L128) — a queued [`QueuedVerifyEmail`](../app/Notifications/Auth/QueuedVerifyEmail.php) notification.

The user is redirected back to `/login` with a flash message:

> Registration submitted. Verify your email, then wait for super admin approval.

### What the new shop owner sees next

```
┌──────────────────────────────────────────┐
│  Registration submitted.                 │
│  Verify your email, then wait for super  │
│  admin approval.                         │
└──────────────────────────────────────────┘
```

They cannot yet log in. Two gates remain: email verification and super-admin approval.

## Email verification

The verification link is signed and routes to [`AuthController@verifyEmail`](../app/Http/Controllers/Auth/AuthController.php#L127-L143):

```
GET /email/verify/{id}/{hash}   middleware: signed
```

The handler:

1. Looks up the user by `id`.
2. Verifies `hash_equals(sha1($user->getEmailForVerification()), $hash)` — same scheme as Laravel's default verification.
3. Calls `markEmailAsVerified()` if not already set.
4. Redirects to `/login` with the message "Email verified successfully. Your account now awaits super admin approval."

> Note: this is a custom controller method, not Laravel's default `EmailVerificationController`. Behaviour is identical, but the redirect message and URL are tailored to the approval workflow.

## Super-admin approval

A new tenant exists in the database with `status = pending` and an inactive admin user. The super admin must take action.

### Admin shops listing

```
GET /admin/shops              → TenantController@index
```

Middleware stack: `web, auth, verified, active.user, central.user, super_admin`.

The controller — [`Admin\TenantController`](../app/Http/Controllers/Admin/TenantController.php) — calls `$this->authorize('viewAny', Tenant::class)` (handled by [`TenantPolicy`](../app/Policies/TenantPolicy.php), which the `Gate::before` super-admin override always allows). It returns all tenants with their admin user, sorted newest-first.

### Status transitions

```
POST /admin/shops/{tenant}/status/{action}   action ∈ approve|reject|suspend|reactivate
```

Validated by [`ChangeTenantStatusRequest`](../app/Http/Requests/Admin/ChangeTenantStatusRequest.php) (only `reason` is required, and only for `reject`).

[`ChangeTenantStatusAction`](../app/Actions/Admin/ChangeTenantStatusAction.php) executes the transition map:

| Action | New status | `is_active` for tenant admin | Side effects |
|--------|-----------|------------------------------|--------------|
| `approve` | `Approved` | `true` | Sets `approved_at`, `approved_by`, `onboarding_status = in_progress`. Sends notification. |
| `reject` | `Rejected` | `false` | Stores `rejected_reason`, `rejected_at`. Sends notification. |
| `suspend` | `Suspended` | `false` | Sets `suspended_at`. Sends notification. |
| `reactivate` | `Approved` | `true` | Re-enables a previously suspended/rejected shop. |

Unknown actions throw [`InvalidTenantStatusTransitionException`](../app/Exceptions/InvalidTenantStatusTransitionException.php), which is rendered to JSON 422 or flashed back via the global handler in [bootstrap/app.php](../bootstrap/app.php#L46-L54).

A queued [`TenantStatusChangedNotification`](../app/Notifications/TenantStatusChangedNotification.php) emails the tenant admin with the new status. After approval, the tenant admin can log in.

## Login

```
GET  /login                         → AuthController@login
POST /login                         → AuthController@loginSubmit  (throttle 5,1)
```

[`AuthController@loginSubmit`](../app/Http/Controllers/Auth/AuthController.php#L48-L87) runs a layered set of checks:

```
1. Auth::attempt($credentials, $remember)
   ├─ failure → flash error, redirect back with old email
   └─ success ↓
2. session->regenerate()
3. !user->hasVerifiedEmail() → logout + "Verify your email…"
4. resolveLoginBlockMessage($user):
     a. employee with no tenant_id → "Employee accounts must belong to a tenant workspace."
     b. user has tenant_id but tenant missing → "Tenant account could not be found."
     c. tenant.status->allowsLogin() == false → status-specific message:
        - pending   → "Your shop is still waiting for super admin approval."
        - rejected  → "Your shop registration was rejected. Please contact support…"
        - suspended → "Your shop has been suspended. Please contact support."
        - inactive  → "Your shop is inactive. Please contact support."
     d. !user->is_active → "Your user account is inactive."
   any block message → logout + warning flash
5. user.failed_attempts = 0, locked_until = null,
   last_login_at = now(), last_login_ip = request->ip()
6. redirect to user->defaultDashboardRouteName()
   - employees: hard redirect (no intended URL)
   - others: redirect()->intended()
```

`logoutBlockedUser()` always invalidates the session before redirecting back to `/login` — the blocked user is never left in a half-authenticated state.

The throttle middleware (`throttle:5,1`) limits login attempts to **5 per minute per IP/email**.

> Note on lockout fields: the `users` table carries `failed_attempts` and `locked_until` columns intended for future programmatic lockout. They're reset on every successful login but the increment/lock logic is not yet wired into `loginSubmit`.

## Password reset

Standard Laravel `Password` facade flow:

```
GET  /forgot                           → AuthController@forgot           (form)
POST /forgot                           → AuthController@sendResetLink    (Password::sendResetLink)
GET  /reset-password/{token}           → AuthController@resetForm        (form with token)
POST /reset-password                   → AuthController@resetPassword    (Password::reset)
```

[`AuthController@resetPassword`](../app/Http/Controllers/Auth/AuthController.php#L108-L125) updates the password, regenerates `remember_token`, and fires `Illuminate\Auth\Events\PasswordReset`. The `password` cast `'hashed'` on the User model means assigning a plain-text password during reset is fine — Laravel hashes it automatically.

Validation: [`ForgotPasswordRequest`](../app/Http/Requests/Auth/ForgotPasswordRequest.php), [`ResetPasswordRequest`](../app/Http/Requests/Auth/ResetPasswordRequest.php).

## Logout

```
POST /logout                           → AuthController@logout    middleware: auth
```

Logs out, invalidates the session, regenerates the CSRF token, redirects to `/login`.

## Impersonation

Two impersonation paths exist; both stash the original user's ID in `session('impersonator_id')` and reuse the same "stop" handler.

### Super admin → tenant admin

```
GET /admin/shops/impersonate/{tenant}     middleware: super_admin
GET /admin/impersonate/stop               middleware: impersonating
```

Implemented in [`Admin\TenantController@impersonate`](../app/Http/Controllers/Admin/TenantController.php#L44-L65). Pre-conditions:

- `TenantPolicy@impersonate` allows it (super admins always pass via `Gate::before`).
- The tenant has an `adminUser`.
- The tenant `isAccessible()` (status allows login).

On success the super admin is logged in *as the tenant admin* and redirected to `tenant.dashboard`. `stopImpersonate()` reads `session('impersonator_id')` and uses `auth()->loginUsingId()` to restore the original session.

### Tenant admin → staff

```
GET /tenant/settings/roles-permissions/staff/{user}/impersonate
```

[`Tenant\RolesPermissionsController@impersonateStaff`](../app/Http/Controllers/Tenant/RolesPermissionsController.php#L221-L243) enforces:

- The target `User.tenant_id` matches the current tenant.
- The actor is not impersonating themselves.
- The target is not a tenant admin or super admin.

On success the tenant admin lands on `employee.dashboard` as the impersonated staff member. The same `/admin/impersonate/stop` route handles the return trip.

The [`EnsureImpersonatingSession`](../app/Http/Middleware/EnsureImpersonatingSession.php) middleware ensures the stop endpoint is only reachable when an impersonation is in progress.

## Diagram — end-to-end onboarding

```
┌────────────────┐
│ Visitor lands  │
│      / (web)   │
└─────┬──────────┘
      │
      ▼
┌────────────────────┐
│ POST /register     │
│ RegisterShopRequest│
└─────┬──────────────┘
      │ DB::transaction
      ▼
┌────────────────────────────────────────────┐
│ tenants:    status = pending               │
│ users:      role = tenant_admin            │
│             is_active = false              │
│ team-scoped Spatie role: tenant_admin      │
└─────┬──────────────────────────────────────┘
      │ event(Registered)
      ▼
┌──────────────────────┐
│ QueuedVerifyEmail    │  ← signed link to /email/verify/{id}/{hash}
└─────┬────────────────┘
      │ user clicks link
      ▼
┌──────────────────────┐
│ markEmailAsVerified  │
└─────┬────────────────┘
      │
      ▼  meanwhile…
┌──────────────────────────────┐
│ Super admin: /admin/shops    │
│ POST .../status/approve      │
└─────┬────────────────────────┘
      │ ChangeTenantStatusAction
      ▼
┌──────────────────────────────────────────────┐
│ tenants.status        = approved             │
│ tenants.approved_at   = now()                │
│ users(tenant_admin).is_active = true         │
│ TenantStatusChangedNotification → email      │
└─────┬────────────────────────────────────────┘
      │
      ▼
┌──────────────────────┐
│ Tenant admin /login  │
│ → tenant.dashboard   │
└──────────────────────┘
```

## Common pitfalls

- **Forgetting to verify before approval.** A user can be approved while still email-unverified; login will block at the `verified` middleware before any tenant check fires. The order of checks in `loginSubmit` is: credentials → email verified → tenant status → active flag.
- **Re-registering with the same email.** `users.email` is unique; the duplicate registration will fail validation in `RegisterShopRequest`. There's no "delete and re-register" flow.
- **Inactive admin after suspend.** `ChangeTenantStatusAction` flips `is_active` on the *tenant admin only*. Other staff under the tenant retain their `is_active = true` flag, but they are still blocked at `tenant.approved` middleware because the tenant status fails the gate.
- **Impersonation loops.** Nesting impersonations is prevented because `session('impersonator_id')` is overwritten — the only way back is to the original ID stored on the first hop.
