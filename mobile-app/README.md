# AutoServe Mobile

Flutter customer portal for AutoServe shops (`pos_mobile`). Signs in against the same Laravel Sanctum API as the web portal (`/api/v1/customer`).

## Docs (for humans & AI agents)

| Doc | Contents |
|-----|----------|
| [AGENTS.md](AGENTS.md) | Agent entrypoint — rules + quick commands |
| [docs/SETUP.md](docs/SETUP.md) | macOS + Windows setup, OS support, run, APK, iOS release |
| [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) | Folders, API client, theming, conventions |
| [docs/SCREENS.md](docs/SCREENS.md) | Routes and screen flows |
| [docs/COMPONENTS.md](docs/COMPONENTS.md) | Theme tokens + reusable widgets |

## Supported OS

| Platform | Versions |
|----------|----------|
| Android | **8.0 – 16** (API 26 – 36) |
| iOS | **14.0 – latest** (Mac + Xcode required to build) |

## Quick start

The Laravel app must be running (`composer run dev` or `php artisan serve`) so login can hit `/api/v1/customer/login`.

**macOS**

```bash
source tooling/env.sh   # from mobile-app/, or: source mobile-app/tooling/env.sh from repo root
flutter pub get
flutter run
# iOS home-screen install:
flutter run --release -d <iphoneId>
# Android shareable APK:
flutter build apk --release
```

**Windows (PowerShell)**

```powershell
. .\tooling\env.ps1     # from mobile-app\, or: . .\mobile-app\tooling\env.ps1 from repo root
flutter pub get
flutter run
flutter build apk --release
```

Windows: Android only. iOS needs macOS + Xcode + Apple signing.

## Demo login (after `php artisan migrate --seed`)

| Field | Value |
|-------|--------|
| Email | `olivia@obtainsolutions.com` |
| Password | `password` |

Physical device: `flutter run --dart-define=API_BASE_URL=http://<your-lan-ip>:8000`

## Status

Customer login, session restore, forgot-password, and home (store credit + recent visits) are wired to the live API. Further portal screens can reuse the same client.
