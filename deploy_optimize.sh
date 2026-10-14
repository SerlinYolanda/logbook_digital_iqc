#!/bin/bash
echo "==================================================="
echo "  LOGBOOK DIGITAL IQC - PRODUCTION OPTIMIZER"
echo "==================================================="

cd app-backend

echo "[1/5] Clearing outdated cache..."
php artisan optimize:clear

echo "[2/5] Caching application configuration..."
php artisan config:cache

echo "[3/5] Caching application routes..."
php artisan route:cache

echo "[4/5] Pre-compiling Blade templates..."
php artisan view:cache

echo "[5/5] Caching application events..."
php artisan event:cache

echo "==================================================="
echo "  PORTAL OPTIMIZED FOR MAXIMUM SPEED & LOW DEPLOY LOAD!"
echo "==================================================="
