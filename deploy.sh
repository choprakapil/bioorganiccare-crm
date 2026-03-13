#!/bin/bash

# Exit on error
set -e

echo "🚀 Starting deployment logic on server..."

# Configuration
PROJECT_ROOT="/home/u721531294/domains/bioorganiccare.com/project"
PUBLIC_HTML="/home/u721531294/domains/bioorganiccare.com/public_html"
DEPLOY_TEMP="/home/u721531294/domains/bioorganiccare.com/deploy_temp"

# ==============================
# 1. Backend (Laravel API)
# ==============================
echo "📦 Deploying Backend (API)..."
cd $PROJECT_ROOT/api

# Install dependencies
composer install --no-dev --optimize-autoloader

# Run migrations
php artisan migrate --force

# Clear and optimize caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

php artisan config:cache
php artisan route:cache

echo "✅ Backend updated."

# ==============================
# 2. Frontend (CRM)
# ==============================
echo "💻 Deploying Frontend (CRM)..."

# Ensure target directory exists
mkdir -p $PUBLIC_HTML/app

# Clean target directory
rm -rf $PUBLIC_HTML/app/*

# Copy build from temp location (transferred via GitHub Action)
if [ -d "$DEPLOY_TEMP/frontend/dist" ]; then
    cp -r $DEPLOY_TEMP/frontend/dist/* $PUBLIC_HTML/app/
    echo "✅ CRM Frontend updated."
else
    echo "⚠️ Warning: CRM build not found in $DEPLOY_TEMP/frontend/dist"
fi

# ==============================
# 3. Landing Page
# ==============================
echo "🏠 Deploying Landing Page..."

# Clean target directory (excluding /app and /api which are handled separately)
# We want to keep /app (CRM) and /api (Laravel public folder)
# Usually Laravel API public folder is symlinked or matched via .htaccess
# According to prompt: API is at /api.
# If /api is a folder in public_html, we should preserve it.

# Safe delete for landing: delete everything except app and api
find $PUBLIC_HTML -maxdepth 1 ! -name 'public_html' ! -name 'app' ! -name 'api' ! -name '.htaccess' -exec rm -rf {} +

# Copy build from temp location
if [ -d "$DEPLOY_TEMP/landing/dist" ]; then
    cp -r $DEPLOY_TEMP/landing/dist/* $PUBLIC_HTML/
    echo "✅ Landing Page updated."
else
    echo "⚠️ Warning: Landing build not found in $DEPLOY_TEMP/landing/dist"
fi

# ==============================
# Cleanup
# ==============================
echo "🧹 Cleaning up temp files..."
rm -rf $DEPLOY_TEMP

echo "🎉 Deployment complete!"