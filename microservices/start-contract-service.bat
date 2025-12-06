@echo off
title Contract Register Service - Port 8015
color 0A
echo ================================================
echo   CONTRACT REGISTER SERVICE
echo   Running on http://localhost:8015
echo ================================================
echo.
echo Service is running... DO NOT CLOSE THIS WINDOW
echo.
cd /d "%~dp0contract-service"
php -S 0.0.0.0:8015 index.php
pause
