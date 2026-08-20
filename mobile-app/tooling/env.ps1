# Load AutoServe Flutter/Android env (Windows PowerShell)
# Dot-source before Flutter/Android commands:
#   . .\mobile-app\tooling\env.ps1
#   . .\tooling\env.ps1          # from mobile-app\

function Test-JavaHome([string]$Path) {
    return (Test-Path -LiteralPath (Join-Path $Path 'bin\java.exe'))
}

if (-not $env:JAVA_HOME) {
    $candidates = @(
        "${env:ProgramFiles}\Android\Android Studio\jbr",
        "${env:ProgramFiles}\Android\Android Studio\jre",
        "${env:LOCALAPPDATA}\Programs\Android Studio\jbr",
        "${env:ProgramFiles}\Eclipse Adoptium\jdk-17*",
        "${env:ProgramFiles}\Java\jdk-17*"
    )
    foreach ($pattern in $candidates) {
        $resolved = Get-Item -Path $pattern -ErrorAction SilentlyContinue | Select-Object -First 1
        if ($null -ne $resolved -and (Test-JavaHome $resolved.FullName)) {
            $env:JAVA_HOME = $resolved.FullName
            break
        }
    }
}

if (-not $env:ANDROID_HOME) {
    $env:ANDROID_HOME = Join-Path $env:LOCALAPPDATA 'Android\Sdk'
}
$env:ANDROID_SDK_ROOT = $env:ANDROID_HOME

if (-not $env:FLUTTER_ROOT) {
    $env:FLUTTER_ROOT = Join-Path $env:USERPROFILE 'development\flutter'
}

if (-not $env:GRADLE_USER_HOME_OVERRIDE) {
    $env:GRADLE_USER_HOME = Join-Path $env:USERPROFILE '.gradle'
} else {
    $env:GRADLE_USER_HOME = $env:GRADLE_USER_HOME_OVERRIDE
}

$pathsToPrepend = @(
    (Join-Path $env:JAVA_HOME 'bin'),
    (Join-Path $env:FLUTTER_ROOT 'bin'),
    (Join-Path $env:ANDROID_HOME 'cmdline-tools\latest\bin'),
    (Join-Path $env:ANDROID_HOME 'platform-tools'),
    (Join-Path $env:ANDROID_HOME 'emulator')
) | Where-Object { $_ -and (Test-Path -LiteralPath $_) }

$env:Path = ($pathsToPrepend + ($env:Path -split ';' | Where-Object { $_ })) -join ';'

Write-Host "JAVA_HOME=$env:JAVA_HOME"
Write-Host "ANDROID_HOME=$env:ANDROID_HOME"
Write-Host "FLUTTER_ROOT=$env:FLUTTER_ROOT"
Write-Host "GRADLE_USER_HOME=$env:GRADLE_USER_HOME"
