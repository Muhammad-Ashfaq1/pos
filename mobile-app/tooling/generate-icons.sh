#!/usr/bin/env bash
# Resize the shared PWA mark into Flutter launcher icons.
# Source of truth: repo public/assets/img/pwa/icon-512.png (same as web/PWA).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
SRC="$ROOT/public/assets/img/pwa/icon-512.png"
MASK="$ROOT/public/assets/img/pwa/icon-maskable-512.png"
APP="$ROOT/mobile-app"

if [ ! -f "$SRC" ]; then
  echo "Missing $SRC" >&2
  exit 1
fi

mkdir -p "$APP/assets/brand"
cp "$SRC" "$APP/assets/brand/icon-512.png"
cp "$MASK" "$APP/assets/brand/icon-maskable-512.png"

ICONSET="$APP/ios/Runner/Assets.xcassets/AppIcon.appiconset"
sips -z 20 20 "$SRC" --out "$ICONSET/Icon-App-20x20@1x.png" >/dev/null
sips -z 40 40 "$SRC" --out "$ICONSET/Icon-App-20x20@2x.png" >/dev/null
sips -z 60 60 "$SRC" --out "$ICONSET/Icon-App-20x20@3x.png" >/dev/null
sips -z 29 29 "$SRC" --out "$ICONSET/Icon-App-29x29@1x.png" >/dev/null
sips -z 58 58 "$SRC" --out "$ICONSET/Icon-App-29x29@2x.png" >/dev/null
sips -z 87 87 "$SRC" --out "$ICONSET/Icon-App-29x29@3x.png" >/dev/null
sips -z 40 40 "$SRC" --out "$ICONSET/Icon-App-40x40@1x.png" >/dev/null
sips -z 80 80 "$SRC" --out "$ICONSET/Icon-App-40x40@2x.png" >/dev/null
sips -z 120 120 "$SRC" --out "$ICONSET/Icon-App-40x40@3x.png" >/dev/null
sips -z 120 120 "$SRC" --out "$ICONSET/Icon-App-60x60@2x.png" >/dev/null
sips -z 180 180 "$SRC" --out "$ICONSET/Icon-App-60x60@3x.png" >/dev/null
sips -z 76 76 "$SRC" --out "$ICONSET/Icon-App-76x76@1x.png" >/dev/null
sips -z 152 152 "$SRC" --out "$ICONSET/Icon-App-76x76@2x.png" >/dev/null
sips -z 167 167 "$SRC" --out "$ICONSET/Icon-App-83.5x83.5@2x.png" >/dev/null
sips -z 1024 1024 "$SRC" --out "$ICONSET/Icon-App-1024x1024@1x.png" >/dev/null

RES="$APP/android/app/src/main/res"
sips -z 48 48 "$SRC" --out "$RES/mipmap-mdpi/ic_launcher.png" >/dev/null
sips -z 72 72 "$SRC" --out "$RES/mipmap-hdpi/ic_launcher.png" >/dev/null
sips -z 96 96 "$SRC" --out "$RES/mipmap-xhdpi/ic_launcher.png" >/dev/null
sips -z 144 144 "$SRC" --out "$RES/mipmap-xxhdpi/ic_launcher.png" >/dev/null
sips -z 192 192 "$SRC" --out "$RES/mipmap-xxxhdpi/ic_launcher.png" >/dev/null

sips -z 192 192 "$SRC" --out "$APP/web/icons/Icon-192.png" >/dev/null
sips -z 512 512 "$SRC" --out "$APP/web/icons/Icon-512.png" >/dev/null
sips -z 192 192 "$MASK" --out "$APP/web/icons/Icon-maskable-192.png" >/dev/null
sips -z 512 512 "$MASK" --out "$APP/web/icons/Icon-maskable-512.png" >/dev/null

echo "Wrote AutoServe launcher icons from $SRC"
