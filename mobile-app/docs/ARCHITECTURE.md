# Architecture

## Layout

```
mobile-app/lib/
├── main.dart                      # session.load() then runApp(PosApp)
├── app/
│   ├── app.dart                   # MaterialApp + named routes
│   ├── routes/app_routes.dart
│   └── theme/                     # design tokens + ThemeData
├── core/
│   ├── app_services.dart          # tiny service locator
│   ├── api/                       # HTTP client, config, errors
│   ├── auth/auth_session.dart     # Sanctum token + shop slug persistence
│   ├── navigation/app_transitions.dart
│   └── widgets/                   # shared UI kit
└── features/
    ├── auth/{data,models,presentation/screens}/
    └── home/{data,models,presentation/screens}/
```

Feature-first. Shared chrome lives in `core/widgets`. Theme tokens live in `app/theme` — prefer tokens over hard-coded hex in screens.

## Layers

| Layer | Status |
|-------|--------|
| Presentation (screens/widgets) | Active |
| Models | `Customer`, `CustomerDashboard`, … |
| Data | `AuthRepository`, `DashboardRepository` |
| HTTP | `ApiClient` → Laravel `/api/v1/customer` |
| DI / global state | `AppServices` + `AuthSession` only |

Keep screens thin. Do not dump HTTP into widgets.

## API

Same endpoints as the web customer portal. Bearer token from `POST /login`.

| Method | Path | Auth |
|--------|------|------|
| POST | `/login` | guest (`email`, `password`, `device_name`) |
| POST | `/forgot-password` | guest |
| POST | `/logout` | Bearer |
| GET | `/me` | Bearer |
| GET | `/dashboard` | Bearer |

Origin: `ApiConfig.baseUrl` (`--dart-define=API_BASE_URL=…` to override).

## State

- Local `StatefulWidget` + `setState`
- Token / last shop slug: `SharedPreferences` via `AuthSession`
- Do not add Provider / Riverpod / Bloc / GetX unless the task explicitly requires it

## Navigation

- Named routes: `AppRoutes` constants ↔ `PosApp.routes`
- Auth → home and splash routing use `AppTransitions.fadeReplace`

## Theming

Lake-theme primary `#696CFF` matches web `pos-theme-lake`. Inter via `google_fonts`.

| File | Role |
|------|------|
| `app_colors.dart` | Palette (`AppColors`) |
| `app_text_styles.dart` | Inter (`AppTextStyles`) |
| `app_spacing.dart` | Spacing / control heights |
| `app_radius.dart` | Corner radii |
| `app_shadows.dart` | Card shadows |
| `app_theme.dart` | `AppTheme.light()` Material 3 + transitions |
| `app_glass.dart` | Glass cards matching web `pos-glass-card` |

## Package / platform

| | |
|--|--|
| Pub name | `pos_mobile` |
| Import prefix | `package:pos_mobile/...` |
| Android applicationId | `com.autoserve.pos_mobile` |
| iOS bundle | `com.autoserve.posMobile` |
| Android OS | **minSdk 26** (8.0) → target/compile **36** (16) |
| iOS OS | **deployment 14.0** → latest |
| Android ABIs | `arm64-v8a`, `armeabi-v7a`, `x86_64` |

## Dependencies (keep lean)

Runtime: `flutter`, `cupertino_icons`, `google_fonts`, `http`, `shared_preferences`, `intl`.

## Conventions for new UI

1. Reuse `AppPrimaryButton`, `AppTextField`, `AppPasswordField`, `AppLogo` before creating one-offs.
2. Prefer `const` constructors where possible.
3. Feature-specific widgets stay under `features/<feature>/presentation/widgets/`.
4. New screen = add constant + map entry + screen file under the right feature.
