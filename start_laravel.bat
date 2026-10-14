@echo off
title Logbook Digital IQC Portal - Laravel Server
echo ========================================================
echo   LOGBOOK DIGITAL IQC PORTAL
echo ========================================================
echo.
echo Menjalankan Laravel Development Server...
echo Alamat: http://127.0.0.1:8000
echo.
echo AKUN ADMINISTRATOR AWAL:
echo - Admin : NIK = ADM-001  ^| Password = password123
echo.
echo Tekan Ctrl+C untuk menghentikan server.
echo ========================================================
echo.

cd /d "%~dp0app-backend"
start "" http://127.0.0.1:8000
php artisan serve --port=8000
pause
