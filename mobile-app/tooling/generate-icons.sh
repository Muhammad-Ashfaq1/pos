#!/usr/bin/env bash
# Render the AutoServe brand mark (public/assets/img/logo/autoserve.svg)
# into web PWA/favicons and Flutter launcher icons.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
SRC="$ROOT/public/assets/img/logo/autoserve.svg"
APP="$ROOT/mobile-app"
RENDER="$APP/tooling/render-app-icon.swift"
ICO="$APP/tooling/png-to-ico.py"
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT

if [ ! -f "$SRC" ]; then
  echo "Missing $SRC" >&2
  exit 1
fi

chmod +x "$RENDER" "$ICO"

MASTER="$TMP/icon-1024.png"
MASK="$TMP/icon-maskable-1024.png"
swift "$RENDER" "$SRC" "$MASTER" 1024 0.12
swift "$RENDER" "$SRC" "$MASK" 1024 0.22

mkdir -p "$APP/assets/brand"
cp "$SRC" "$APP/assets/brand/autoserve.svg"
cp "$MASTER" "$APP/assets/brand/icon-512.png"
# Keep a 512 master next to the SVG for tooling that expects a PNG.
sips -z 512 512 "$MASTER" --out "$APP/assets/brand/icon-512.png" >/dev/null
sips -z 512 512 "$MASK" --out "$APP/assets/brand/icon-maskable-512.png" >/dev/null

# --- Web PWA + favicons (Laravel) ---
PWA="$ROOT/public/assets/img/pwa"
FAV="$ROOT/public/assets/img/favicon"
mkdir -p "$PWA" "$FAV"

sips -z 192 192 "$MASTER" --out "$PWA/icon-192.png" >/dev/null
sips -z 512 512 "$MASTER" --out "$PWA/icon-512.png" >/dev/null
sips -z 512 512 "$MASK" --out "$PWA/icon-maskable-512.png" >/dev/null
sips -z 180 180 "$MASTER" --out "$PWA/apple-touch-icon.png" >/dev/null

sips -z 32 32 "$MASTER" --out "$FAV/favicon-32.png" >/dev/null
sips -z 64 64 "$MASTER" --out "$FAV/favicon.png" >/dev/null
sips -z 180 180 "$MASTER" --out "$FAV/apple-touch-icon.png" >/dev/null
python3 "$ICO" "$FAV/favicon-32.png" "$FAV/favicon.ico"

# --- Flutter iOS ---
ICONSET="$APP/ios/Runner/Assets.xcassets/AppIcon.appiconset"
sips -z 20 20 "$MASTER" --out "$ICONSET/Icon-App-20x20@1x.png" >/dev/null
sips -z 40 40 "$MASTER" --out "$ICONSET/Icon-App-20x20@2x.png" >/dev/null
sips -z 60 60 "$MASTER" --out "$ICONSET/Icon-App-20x20@3x.png" >/dev/null
sips -z 29 29 "$MASTER" --out "$ICONSET/Icon-App-29x29@1x.png" >/dev/null
sips -z 58 58 "$MASTER" --out "$ICONSET/Icon-App-29x29@2x.png" >/dev/null
sips -z 87 87 "$MASTER" --out "$ICONSET/Icon-App-29x29@3x.png" >/dev/null
sips -z 40 40 "$MASTER" --out "$ICONSET/Icon-App-40x40@1x.png" >/dev/null
sips -z 80 80 "$MASTER" --out "$ICONSET/Icon-App-40x40@2x.png" >/dev/null
sips -z 120 120 "$MASTER" --out "$ICONSET/Icon-App-40x40@3x.png" >/dev/null
sips -z 120 120 "$MASTER" --out "$ICONSET/Icon-App-60x60@2x.png" >/dev/null
sips -z 180 180 "$MASTER" --out "$ICONSET/Icon-App-60x60@3x.png" >/dev/null
sips -z 76 76 "$MASTER" --out "$ICONSET/Icon-App-76x76@1x.png" >/dev/null
sips -z 152 152 "$MASTER" --out "$ICONSET/Icon-App-76x76@2x.png" >/dev/null
sips -z 167 167 "$MASTER" --out "$ICONSET/Icon-App-83.5x83.5@2x.png" >/dev/null
sips -z 1024 1024 "$MASTER" --out "$ICONSET/Icon-App-1024x1024@1x.png" >/dev/null

LAUNCH="$APP/ios/Runner/Assets.xcassets/LaunchImage.imageset"
sips -z 168 168 "$MASTER" --out "$LAUNCH/LaunchImage.png" >/dev/null
sips -z 336 336 "$MASTER" --out "$LAUNCH/LaunchImage@2x.png" >/dev/null
sips -z 504 504 "$MASTER" --out "$LAUNCH/LaunchImage@3x.png" >/dev/null

# --- Flutter Android ---
RES="$APP/android/app/src/main/res"
sips -z 48 48 "$MASTER" --out "$RES/mipmap-mdpi/ic_launcher.png" >/dev/null
sips -z 72 72 "$MASTER" --out "$RES/mipmap-hdpi/ic_launcher.png" >/dev/null
sips -z 96 96 "$MASTER" --out "$RES/mipmap-xhdpi/ic_launcher.png" >/dev/null
sips -z 144 144 "$MASTER" --out "$RES/mipmap-xxhdpi/ic_launcher.png" >/dev/null
sips -z 192 192 "$MASTER" --out "$RES/mipmap-xxxhdpi/ic_launcher.png" >/dev/null

# --- Flutter web ---
sips -z 192 192 "$MASTER" --out "$APP/web/icons/Icon-192.png" >/dev/null
sips -z 512 512 "$MASTER" --out "$APP/web/icons/Icon-512.png" >/dev/null
sips -z 192 192 "$MASK" --out "$APP/web/icons/Icon-maskable-192.png" >/dev/null
sips -z 512 512 "$MASK" --out "$APP/web/icons/Icon-maskable-512.png" >/dev/null
sips -z 32 32 "$MASTER" --out "$APP/web/favicon.png" >/dev/null

echo "Wrote AutoServe icons from $SRC"
