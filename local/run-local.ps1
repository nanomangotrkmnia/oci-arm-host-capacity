$ErrorActionPreference = 'SilentlyContinue'

$repo = Split-Path -Parent $PSScriptRoot
Set-Location -LiteralPath $repo

$php = (Get-Command php.exe -ErrorAction SilentlyContinue | Select-Object -First 1).Source
if (-not $php) {
    $php = Join-Path $env:LOCALAPPDATA 'Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe'
}

$stamp = (Get-Date).ToUniversalTime().ToString('yyyy-MM-ddTHH:mm:ssZ')
$output = & $php index.php 2>&1 | Out-String

Add-Content -LiteralPath (Join-Path $repo 'oci.log') -Value "=== $stamp ===`r`n$output"

if (Test-Path -LiteralPath (Join-Path $repo 'verbose.on')) {
    "OCI ARM attempt [$stamp]`n$($output.Trim())" | & $php verbose-send.php
}

if ($output -match 'ocid1\.instance|Already have an instance') {
    schtasks /Change /TN 'OCI ARM Capacity' /DISABLE 2>&1 | Out-Null
}
