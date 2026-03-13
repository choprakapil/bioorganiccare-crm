#!/bin/bash

echo "🚀 Starting deployment..."

# ==============================
# Backend (Laravel)
# ==============================
cd backend

echo "Installing composer dependencies..."
composer install --no-dev --optimize-autoloader

echo "Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "Running migrations..."
php artisan migrate --force

cd ..

# ==============================
# Frontend (React / Vite)
# ==============================
cd frontend

echo "Installing node modules..."
npm install

echo "Building frontend..."
npm run build

cd ..

# ==============================
# Deploy frontend build
# ==============================

echo "Deploying frontend to /app..."

rm -rf /public_html/app/*
cp -r frontend/dist/* /public_html/app/

echo "✅ Deployment complete!"