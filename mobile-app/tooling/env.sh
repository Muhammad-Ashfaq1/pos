#!/usr/bin/env bash
# macOS / Linux / Git Bash — source before Flutter/Android commands:
#   source mobile-app/tooling/env.sh
# Windows PowerShell developers: use tooling/env.ps1 instead.

if [ -z "${JAVA_HOME:-}" ]; then
  if [ -x "/Applications/Android Studio.app/Contents/jbr/Contents/Home/bin/java" ]; then
    export JAVA_HOME="/Applications/Android Studio.app/Contents/jbr/Contents/Home"
  else
    export JAVA_HOME="$HOME/Library/Java/JavaVirtualMachines/temurin-17.jdk/Contents/Home"
  fi
fi

export ANDROID_HOME="${ANDROID_HOME:-$HOME/Library/Android/sdk}"
export ANDROID_SDK_ROOT="$ANDROID_HOME"
export FLUTTER_ROOT="${FLUTTER_ROOT:-$HOME/development/flutter}"
# Force real Gradle home — Cursor agent shells inject a sandbox cache path
# that stalls first-time Android builds.
export GRADLE_USER_HOME="${GRADLE_USER_HOME_OVERRIDE:-$HOME/.gradle}"

if command -v ruby >/dev/null 2>&1; then
  GEM_BIN="$(ruby -e 'print Gem.user_dir' 2>/dev/null)/bin"
  [ -d "$GEM_BIN" ] && export PATH="$GEM_BIN:$PATH"
fi

export PATH="$JAVA_HOME/bin:$FLUTTER_ROOT/bin:/usr/local/bin:$ANDROID_HOME/cmdline-tools/latest/bin:$ANDROID_HOME/platform-tools:$ANDROID_HOME/emulator:$PATH"
