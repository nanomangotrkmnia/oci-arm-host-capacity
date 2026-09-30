$ErrorActionPreference = 'SilentlyContinue'

$repo = Split-Path -Parent $PSScriptRoot
Set-Location -LiteralPath $repo

$php = (Get-Command php.exe -ErrorAction SilentlyContinue | Select-Object -First 1).Source
if (-not $php) {
    $php = Join-Path $env:LOCALAPPDATA 'Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe'
}

$logPath = Join-Path $repo 'oci.log'
$content = ''
if (Test-Path -LiteralPath $logPath) {
    $content = Get-Content -Raw -LiteralPath $logPath
}

$attempts = ([regex]::Matches($content, '=== ')).Count

if ($content -match 'ocid1\.instance|Already have an instance') {
    Disable-ScheduledTask -TaskName 'OCI ARM Capacity' -ErrorAction SilentlyContinue | Out-Null
    Remove-Item -LiteralPath $logPath -Force
    exit
}

if ($attempts -eq 0) {
    exit
}

$lastLine = ($content -split "`n" | Where-Object { $_ -match 'host capacity|TooManyRequests|"id"\s*:' } | Select-Object -Last 1)
$lastLine = if ($lastLine) { $lastLine.Trim() } else { 'no response' }

$env:NOTIFY_MESSAGE = "OCI ARM: $attempts attempt(s) in the last hour, still no capacity. Last: $lastLine"
$env:NOTIFY_NO_GIF = '1'

& $php notify.php

if (Test-Path -LiteralPath $logPath) {
    Remove-Item -LiteralPath $logPath -Force
}
