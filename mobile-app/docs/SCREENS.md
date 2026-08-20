# Screens & navigation

Routes are defined in `lib/app/routes/app_routes.dart` and wired in `lib/app/app.dart`.

## Route table

| Constant | Path | Screen | File |
|----------|------|--------|------|
| `splash` | `/` | `SplashScreen` | `features/auth/.../splash_screen.dart` |
| `login` | `/login` | `LoginScreen` | `.../login_screen.dart` |
| `forgotPassword` | `/forgot-password` | `ForgotPasswordScreen` | `.../forgot_password_screen.dart` |
| `home` | `/home` | `HomeScreen` | `features/home/.../home_screen.dart` |

Initial route: **`/`** (splash).

## Auth flow

```
Splash
  ├─ valid Sanctum token (GET /me) → Home (fadeReplace)
  └─ no / invalid token           → Login (fadeReplace)

Login (shop + email + password)
  └─ POST /login → Home (fadeReplace)

Forgot password (shop + email)
  └─ POST /forgot-password → snackbar → back to Login

Home → Sign out → POST /logout → Login (fadeReplace)
```

Shop code is the tenant **slug**. It is remembered locally after a successful attempt so the next visit can skip retyping it.

Login field errors map Laravel 422 `errors.shop` / `errors.email` / `errors.password`.

## Home

Glass dashboard (`pos-glass` tones) via `GET /dashboard?recent_limit=8`:

- Profile strip, store-credit hero (unlock progress)
- Stats: visits, lifetime spend, avg visit, last visit, loyalty points, vehicles
- Vehicles on file, recent credit ledger, recent visits (vehicle, items, credit earned)

Unauthenticated (401) returns to login. Pull-to-refresh reloads.
