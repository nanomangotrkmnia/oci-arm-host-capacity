@echo off
cd /d "%~dp0"
if not exist node_modules (
    call npm install
)
:loop
node bot.js
timeout /t 5 /nobreak >nul
goto loop
