# Script PowerShell per avviare il server PHP backend

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Avvio server PHP backend" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$scriptPath = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $scriptPath

Write-Host "Server in ascolto su http://127.0.0.1:8000" -ForegroundColor Green
Write-Host "Premi CTRL+C per fermare il server" -ForegroundColor Yellow
Write-Host ""

php -S 127.0.0.1:8000 -t public
