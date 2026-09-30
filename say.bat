@echo off
cd /d "%~dp0"

where php >nul 2>nul
if %errorlevel%==0 (
    php say.php
) else (
    "%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe" say.php
)

pause
