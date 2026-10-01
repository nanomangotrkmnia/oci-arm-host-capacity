@echo off
cd /d "%~dp0"

if not exist "%~dp0bot\node_modules" (
    echo Installing bot dependencies...
    pushd "%~dp0bot"
    call npm install
    popd
)

powershell -NoProfile -ExecutionPolicy Bypass -Command "Enable-ScheduledTask -TaskName 'OCI ARM Capacity' -ErrorAction SilentlyContinue | Out-Null; Enable-ScheduledTask -TaskName 'OCI ARM Summary' -ErrorAction SilentlyContinue | Out-Null"

start "" wscript.exe "%~dp0bot\run-hidden.vbs"

echo Started: scheduled tasks enabled, bot launched (system tray).
ping -n 4 127.0.0.1 >nul
