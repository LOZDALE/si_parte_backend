@echo off
echo ========================================
echo Avvio server PHP backend
echo ========================================
echo.
cd /d "%~dp0"
echo Server in ascolto su http://127.0.0.1:8000
echo Premi CTRL+C per fermare il server
echo.
php -S 127.0.0.1:8000 -t public
pause
