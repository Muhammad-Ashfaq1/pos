# Components & classes

Reuse these before adding new primitives.

## Theme tokens (`lib/app/theme/`)

| Class | Purpose |
|-------|---------|
| `AppColors` | Brand + UI colors (lake primary `#696CFF`) |
| `AppTextStyles` | Inter (`AppTextStyles`) |
| `AppSpacing` | 4–40 scale, screen insets, button / touch heights |
| `AppRadius` | 8–28, pill, full |
| `AppShadows` | `card`, `soft` |
| `AppTheme` | `AppTheme.light()` → Material 3 + `pageTransitionsTheme` |

## Navigation (`lib/core/navigation/`)

| Class / API | Use |
|-------------|-----|
| `AppTransitions.pageTheme` | Cupertino slide (Android/iOS) |
| `AppTransitions.fadeReplace` | Soft fade replace (splash / auth → home) |

## Core widgets (`lib/core/widgets/`)

| Class | File | Use |
|-------|------|-----|
| `AppPrimaryButton` | `app_buttons.dart` | Full-width pill CTA; `isLoading` |
| `AppBackButton` | `app_buttons.dart` | Back chrome |
| `AppTextField` / `AppPasswordField` | `app_text_field.dart` | Status-aware fields |
| `AppLogo` / `DottedLoader` | `app_logo.dart` | Brand mark + splash spinner |
| `AppAuthHeader` | `app_controls.dart` | Auth title + subtitle |
| `AppGlassCard` / `AppGlassStat` / `AppGlassBackdrop` | `app_glass.dart` | Web-matching glass surfaces (`pos-tone-*`) |

## API / session

| Class | File |
|-------|------|
| `ApiConfig` | `core/api/api_config.dart` |
| `ApiClient` / `ApiException` | `core/api/` |
| `AuthSession` | `core/auth/auth_session.dart` |
| `AppServices` | `core/app_services.dart` |
