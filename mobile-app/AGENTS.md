# AutoServe Mobile — Agent Guide

Short map for AI agents working in `mobile-app/` only. Deeper pages live in [`docs/`](docs/).

| Doc | Use when |
|-----|----------|
| [docs/SETUP.md](docs/SETUP.md) | **macOS + Windows** setup, OS support, run/build, APK/IPA |
| [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) | Folders, API client, state, what not to invent |
| [docs/SCREENS.md](docs/SCREENS.md) | Routes and screen flows |
| [docs/COMPONENTS.md](docs/COMPONENTS.md) | Theme tokens + reusable widgets |

## Product

Flutter app **AutoServe** (`pos_mobile` `1.0.0+1`) — customer portal for shop visitors. Login talks to Laravel Sanctum at `/api/v1/customer`. Brand color is lake-theme primary `#696CFF`.

## Supported device OS

| Platform | Range | Config |
|----------|-------|--------|
| Android | **8.0 → 16** (API 26 → 36) | `android/app/build.gradle.kts` `minSdk = 26` |
| iOS | **14.0 → latest** | `IPHONEOS_DEPLOYMENT_TARGET = 14.0` |

## Hard rules for agents

1. **Always** load the OS env script before `flutter` / `adb` / Gradle:
   - macOS: `source mobile-app/tooling/env.sh`
   - Windows: `. .\mobile-app\tooling\env.ps1`
2. Prefer existing theme + `lib/core/widgets/` — do not invent parallel buttons/fields/colors.
3. Feature code under `lib/features/<feature>/…`; shared UI under `lib/core/widgets/`.
4. HTTP lives in `lib/core/api/` + feature `data/` repositories — never dump HTTP into widgets.
5. Navigation: named routes in `AppRoutes` + `PosApp.routes` — keep in sync.
6. State today is local `setState` plus `AuthSession` — do not add Riverpod/Bloc/GetX unless asked.
7. Do not edit Laravel/`public/` except when syncing brand icons into mobile assets.
8. **iOS only on macOS.** Windows = Android (or web) only.
9. For iOS **home-screen** installs use `--release` (debug cannot launch from the icon on iOS 14+).
10. For shareable Android APK use `flutter build apk --release` (**fat APK**, all ABIs) — avoid arm64-only unless asked.

## Quick commands

### macOS

```bash
source mobile-app/tooling/env.sh
cd mobile-app
flutter pub get
flutter devices
flutter run -d <deviceId>                    # Android or iOS debug
flutter run --release -d <iphoneId>          # iOS home-screen capable
flutter build apk --release                  # Android 8–16 fat APK
```

### Windows (PowerShell)

```powershell
. .\mobile-app\tooling\env.ps1
cd mobile-app
flutter pub get
flutter devices
flutter run -d <deviceId>
flutter build apk --release
```

## IDs

| Platform | ID |
|----------|-----|
| Android applicationId | `com.autoserve.pos_mobile` |
| iOS bundle | `com.autoserve.posMobile` |
| Display name | AutoServe |
