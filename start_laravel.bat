@echo off
title Logbook Digital IQC Portal - PT Osi Electronics Batam
setlocal

:: Auto-detect PHP from XAMPP or Laragon if not currently in PATH
where php >nul 2>nul
if %errorlevel% neq 0 (
    if exist "C:\xampp\php\php.exe" (
        set "PATH=C:\xampp\php;%PATH%"
    )
)

echo ========================================================
echo   SISTEM LOGBOOK DIGITAL IQC - PT OSI ELECTRONICS BATAM
echo   Proposal Skripsi D4 Teknik Informatika - Serlin Yolanda
echo ========================================================
echo.
echo Menjalankan Laravel Development Server...
echo Alamat Portal: http://127.0.0.1:8000
echo.
echo AKUN PENGGUNA DEMO (Sandi default: password123):
echo - Admin Sistem           : NIK = ADM-001
echo - Master Data / Logistik : NIK = LOG-001
echo - Supervisor IQC         : NIK = SPV-001
echo - Inspector EE Part      : NIK = INS-EE-001
echo - Inspector Automotive   : NIK = INS-AUTO-001
echo - Inspector Wire Harness : NIK = INS-WH-001
echo.
echo Tekan Ctrl+C untuk menghentikan server.
echo ========================================================
echo.

cd /d "%~dp0app-backend"
start "" http://127.0.0.1:8000
php artisan serve --host=127.0.0.1 --port=8000
pause
