# Flutter setup (AutoServe mobile)

Works for **macOS** and **Windows** developers/agents. iOS builds require a Mac + Xcode.

The Laravel backend must be reachable. Local default: `php artisan serve` on port **8000**.

## Supported OS versions

| App platform | Minimum | Maximum / current target |
|--------------|---------|---------------------------|
| **Android** | **8.0 (API 26)** | **16 (API 36)** — `minSdk = 26`, Flutter `targetSdk`/`compileSdk` = 36 |
| **iOS** | **14.0** | Latest — `IPHONEOS_DEPLOYMENT_TARGET = 14.0` |

ABIs in the release APK: `arm64-v8a`, `armeabi-v7a`, `x86_64`.

## Platform matrix (dev machines)

| Capability | macOS | Windows |
|------------|-------|---------|
| Android (device / emulator / APK) | Yes | Yes |
| iOS (device / simulator / IPA) | Yes (Xcode + signing) | **No** — use a Mac |
| Env script | `tooling/env.sh` | `tooling/env.ps1` |

## Prerequisites

### Both OS

| Tool | Notes |
|------|--------|
| Flutter SDK | Stable channel, Dart ≥ 3.5 (`pubspec` requires `sdk: ^3.5.0`) |
| Android Studio | Install SDK + platform-tools; prefer bundled JBR for `JAVA_HOME` |
| Android SDK | Platforms through **API 36**; accept licenses |
| Git | Required by Flutter |
| Chrome (optional) | Flutter web |
| Running Laravel app | Login calls `POST /api/v1/customer/login` |

### macOS only

| Tool | Notes |
|------|--------|
| Xcode (full app) | Not only Command Line Tools; needed for iOS |
| Apple signing | Personal Team / Apple Development cert for device install |

### Windows only

| Tool | Notes |
|------|--------|
| PowerShell 5+ or 7 | Use `env.ps1` |
| USB drivers | OEM driver if `adb devices` empty |

Default paths (override with env vars):

| Variable | macOS default | Windows default |
|----------|---------------|-----------------|
| `FLUTTER_ROOT` | `$HOME/development/flutter` | `%USERPROFILE%\development\flutter` |
| `ANDROID_HOME` | `$HOME/Library/Android/sdk` | `%LOCALAPPDATA%\Android\Sdk` |
| `JAVA_HOME` | Android Studio JBR, else Temurin 17 | Android Studio `jbr` |
| `GRADLE_USER_HOME` | `$HOME/.gradle` | `%USERPROFILE%\.gradle` |

## Environment (required in agent shells)

### macOS / Linux / Git Bash

```bash
source mobile-app/tooling/env.sh
# from inside mobile-app/:
source tooling/env.sh
```

### Windows PowerShell

```powershell
. .\mobile-app\tooling\env.ps1
# from inside mobile-app\:
. .\tooling\env.ps1
```

Sets `JAVA_HOME`, `ANDROID_HOME` / `ANDROID_SDK_ROOT`, `FLUTTER_ROOT`, `GRADLE_USER_HOME`, and Flutter/SDK tools on `PATH`.

## API URL

| Runtime | Default origin |
|---------|----------------|
| iOS simulator / macOS / web | `http://127.0.0.1:8000` |
| Android emulator | `http://10.0.2.2:8000` |
| Physical device | Must pass `--dart-define=API_BASE_URL=http://<lan-ip>:8000` |

```bash
flutter run --dart-define=API_BASE_URL=http://192.168.1.10:8000
```

`.test` Herd hosts do **not** resolve inside the Android emulator — use `10.0.2.2:8000` (artisan serve) or the machine LAN IP.

## First-time project setup

### macOS

```bash
source mobile-app/tooling/env.sh
cd mobile-app
flutter doctor
flutter pub get
```

Then once in Xcode (`open ios/Runner.xcworkspace`):

1. **Signing & Capabilities** → Team = your Apple ID (Personal Team)
2. On device: enable **Developer Mode**; after first install **Trust** the developer profile

### Windows

```powershell
. .\mobile-app\tooling\env.ps1
cd mobile-app
flutter doctor
flutter pub get
```

```bash
flutter doctor --android-licenses
```

## Run on device / emulator

### Android (both OS)

```bash
# macOS
source tooling/env.sh && flutter devices && flutter run -d <deviceId>

# Windows PowerShell
. .\tooling\env.ps1; flutter devices; flutter run -d <deviceId>
```

USB: Developer options → USB debugging.

### iOS (macOS only)

**Debug** (hot reload):

```bash
flutter run -d <iphoneId>
```

**Release** (opens from home screen):

```bash
flutter run --release -d <iphoneId>
```

## Release APK (Android share — both OS)

```bash
# macOS
source mobile-app/tooling/env.sh
cd mobile-app
flutter build apk --release

# Windows
. .\tooling\env.ps1
cd mobile-app
flutter build apk --release
```

Output: `build/app/outputs/flutter-apk/app-release.apk`

| Setting | Value |
|---------|--------|
| minSdk | **26** (Android 8) |
| target/compile | **36** (Android 16) via Flutter |
| ABIs | `arm64-v8a`, `armeabi-v7a`, `x86_64` |
| Signing | Debug keystore (internal share only — not Play Store) |

## Demo accounts

After `php artisan migrate --seed` (see [customer-portal.md](../../docs/customer-portal.md)):

| Email | Password |
|-------|----------|
| `olivia@obtainsolutions.com` | `password` |
| `marcus@obtainsolutions.com` | `password` |
| `priya@obtainsolutions.com` | `password` |

## Common failures

| Symptom | OS | Fix |
|---------|-----|-----|
| `flutter` not found | Both | Run `env.sh` / `env.ps1`; check `FLUTTER_ROOT` |
| Gradle hangs in Cursor | Both | Real `GRADLE_USER_HOME` via env script |
| Login: could not reach the shop | Both | Laravel not running, or wrong `API_BASE_URL` |
| Android emulator login fails | Android | Use `10.0.2.2:8000`, not `localhost` or `pos.test` |
| Black screen from home icon | iOS | Install **release** build, not debug |
| Untrusted developer | iOS | Settings → VPN & Device Management → Trust |

## Icons

Source of truth: the AutoServe brand mark `public/assets/img/logo/autoserve.svg` (same mark as the web navbar).

That script writes:

- Web PWA: `public/assets/img/pwa/`
- Web favicons: `public/assets/img/favicon/`
- Android: `android/app/src/main/res/mipmap-*/ic_launcher.png`
- iOS: `ios/Runner/Assets.xcassets/AppIcon.appiconset/`
- Flutter web: `web/icons/` and `web/favicon.png`

Regenerate (macOS):

```bash
mobile-app/tooling/generate-icons.sh
```

Hot reload does **not** update launcher icons — full reinstall after icon changes.

## Layout reminder

Work only under `mobile-app/`. Entry: `lib/main.dart` → `PosApp`.
