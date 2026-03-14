#!/bin/bash
set -euo pipefail
 
# ==============================================================================
# PROFESSIONAL GIT-BASED ATOMIC DEPLOYMENT SYSTEM (HOSTINGER HARDENED)
# Project: BioOrganicCare CRM
# Features: Zero-Downtime, Local Builds, PHP 8.4, Auto-Rollback, DB Backup
# ==============================================================================
 
# Configuration
SERVER="u721531294@46.202.182.65"
PORT="65002"
REPO_URL="https://github.com/choprakapil/bioorganiccare-crm.git"
REMOTE_BASE="/home/u721531294/domains/bioorganiccare.com"
RELEASES_DIR="$REMOTE_BASE/releases"
SHARED_DIR="$REMOTE_BASE/shared"
CURRENT_SYM="$REMOTE_BASE/current"
PUBLIC_HTML="$REMOTE_BASE/public_html"
TIMESTAMP=$(date +%Y%m%d%H%M%S)
RELEASE_NAME="release_$TIMESTAMP"
RELEASE_PATH="$RELEASES_DIR/$RELEASE_NAME"
 
# Server Binary Paths
PHP_BIN="/opt/alt/php84/usr/bin/php"
COMPOSER_BIN="/usr/local/bin/composer"
 
# Colors
GREEN='\033[0;32m'
RED='\033[1;31m'
YELLOW='\033[1;33m'
NC='\033[0m'
 
echo -e "${YELLOW}🚀 Starting Hardened Hostinger Deployment...${NC}"
 
# 1. Local Frontend Builds
echo -e "${YELLOW}📦 Building CRM Frontend LOCALLY...${NC}"
cd frontend || exit
npm install --legacy-peer-deps
npm run build
cd ..
 
echo -e "${YELLOW}📦 Building Landing Page LOCALLY...${NC}"
cd landing || exit
npm install --legacy-peer-deps
npm run build
cd ..
 
# 2. Remote Preparation & Backend Clone
echo -e "${YELLOW}🔧 Preparing remote structure & Cloning repo...${NC}"
ssh -p $PORT $SERVER << EOF
  mkdir -p $RELEASES_DIR $SHARED_DIR/storage $SHARED_DIR/backups
  mkdir -p $SHARED_DIR/storage/logs $SHARED_DIR/storage/framework/sessions $SHARED_DIR/storage/framework/views $SHARED_DIR/storage/framework/cache $SHARED_DIR/storage/app
  chmod -R 775 $SHARED_DIR/storage
  git clone --depth 1 $REPO_URL $RELEASE_PATH
EOF
 
# 3. Upload Local Builds to Release Path
echo -e "${YELLOW}⬆ Uploading built assets...${NC}"
ssh -p $PORT $SERVER "mkdir -p $RELEASE_PATH/public/app $RELEASE_PATH/public/api"
rsync -avz -e "ssh -p $PORT" frontend/dist/ $SERVER:$RELEASE_PATH/public/app/
rsync -avz -e "ssh -p $PORT" landing/dist/ $SERVER:$RELEASE_PATH/public/
 
# 4. DB Backup (Non-fatal – deployment continues even if backup fails)
echo -e "${YELLOW}💾 Backing up database...${NC}"
ssh -p $PORT $SERVER << EOF
  if [ -f "$SHARED_DIR/.env" ]; then
    DB_NAME=\$(grep '^DB_DATABASE=' $SHARED_DIR/.env | cut -d '=' -f2- | tr -d '\r' | sed 's/^"//;s/"\$//')
    DB_USER=\$(grep '^DB_USERNAME=' $SHARED_DIR/.env | cut -d '=' -f2- | tr -d '\r' | sed 's/^"//;s/"\$//')
    DB_PASS=\$(grep '^DB_PASSWORD=' $SHARED_DIR/.env | cut -d '=' -f2- | tr -d '\r' | sed 's/^"//;s/"\$//')

    if [ ! -z "\$DB_NAME" ]; then
      echo "Dumping \$DB_NAME..."

      DUMP_CMD=""
      if command -v mariadb-dump >/dev/null 2>&1; then
        DUMP_CMD="mariadb-dump"
      elif command -v mysqldump >/dev/null 2>&1; then
        DUMP_CMD="mysqldump"
      else
        echo "⚠ No database dump utility (mariadb-dump or mysqldump) found. Skipping backup."
      fi

      if [ ! -z "\$DUMP_CMD" ]; then
        if ! "\$DUMP_CMD" --no-tablespaces -u "\$DB_USER" -p"\$DB_PASS" "\$DB_NAME" > "$SHARED_DIR/backups/backup_$TIMESTAMP.sql"; then
          echo "⚠ Database backup failed using \$DUMP_CMD. Deployment will continue without a fresh backup."
          rm -f "$SHARED_DIR/backups/backup_$TIMESTAMP.sql"
        else
          echo "✅ Backup saved."
        fi
      fi
    fi
  fi
EOF
 
# 5. Remote Backend Tasks (PHP 8.4)
echo -e "${YELLOW}🔧 Installing backend & optimizing (PHP 8.4)...${NC}"
ssh -p $PORT $SERVER << EOF
  cd $RELEASE_PATH/api

  # Link shared resources (3 levels deep)
  ln -sfn ../../../shared/.env .env
  rm -rf storage
  ln -sfn ../../../shared/storage storage
  mkdir -p bootstrap/cache storage/logs storage/framework/cache storage/framework/sessions storage/framework/views storage/app
  rm -f bootstrap/cache/*.php
  
  # Install dependencies with explicit PHP 8.4
  $PHP_BIN $COMPOSER_BIN install --no-dev --optimize-autoloader --no-scripts
 
  # Run package discovery manually
  $PHP_BIN artisan package:discover --ansi
 
  # Move original Laravel public assets to the public/api folder (including .htaccess)
  cp -r public/. ../public/api/
 
  # Production Fix: Storage link inside public/api
  cd ../public/api
  rm -f storage
  ln -sfn ../../api/storage/app/public storage
 
  # Laravel Optimization (PHP 8.4)
  cd ../../api
  $PHP_BIN artisan migrate --force
  $PHP_BIN artisan optimize:clear
  $PHP_BIN artisan config:cache
  $PHP_BIN artisan route:cache
  $PHP_BIN artisan view:cache
EOF
 
# 6. Atomic Release Switch
echo -e "${YELLOW}🔄 Activating release (Atomic Switch)...${NC}"
ssh -p $PORT $SERVER << EOF
  ln -sfn $RELEASE_PATH $CURRENT_SYM
  rm -rf $PUBLIC_HTML
  ln -sfn $CURRENT_SYM/public $PUBLIC_HTML
EOF
 
# 7. Health Check
echo -e "${YELLOW}🔍 Performing Health Check (https://bioorganiccare.com/api/health)...${NC}"
HEALTH_RESPONSE=$(curl -s -k -i https://bioorganiccare.com/api/health)
HEALTH_STATUS=$(echo "$HEALTH_RESPONSE" | grep HTTP | tail -n 1 | awk '{print $2}')
HEALTH_BODY=$(echo "$HEALTH_RESPONSE" | sed '1,/^\r$/d')
 
if [ "$HEALTH_STATUS" == "200" ]; then
    echo -e "${GREEN}✅ Health Check Passed! Status: $HEALTH_STATUS${NC}"

    echo -e "${YELLOW}🔐 Verifying Auth Contract (POST /api/login invalid creds)...${NC}"
    LOGIN_STATUS=$(curl -s -k -o /tmp/bioorganiccare_login_check.json -w "%{http_code}" \
      -X POST https://bioorganiccare.com/api/login \
      -H "Accept: application/json" \
      -H "Content-Type: application/json" \
      -d '{"email":"invalid@example.com","password":"invalid-password"}')
    LOGIN_BODY=$(cat /tmp/bioorganiccare_login_check.json)

    echo -e "${YELLOW}🔐 Verifying Auth Contract (GET /api/me unauthenticated)...${NC}"
    ME_STATUS=$(curl -s -k -o /tmp/bioorganiccare_me_check.json -w "%{http_code}" \
      https://bioorganiccare.com/api/me \
      -H "Accept: application/json")
    ME_BODY=$(cat /tmp/bioorganiccare_me_check.json)

    if [ "$LOGIN_STATUS" != "422" ] || [ "$ME_STATUS" != "401" ]; then
        echo -e "${RED}❌ Auth Contract Check Failed.${NC}"
        echo -e "${RED}POST /api/login status: $LOGIN_STATUS${NC}"
        echo -e "${RED}POST /api/login body: $LOGIN_BODY${NC}"
        echo -e "${RED}GET /api/me status: $ME_STATUS${NC}"
        echo -e "${RED}GET /api/me body: $ME_BODY${NC}"
        echo -e "${YELLOW}🔄 Rolling back...${NC}"
        ssh -p $PORT $SERVER << EOF
          cd $RELEASES_DIR
          PREVIOUS=\$(ls -1t | sed -n '2p')
          if [ ! -z "\$PREVIOUS" ]; then
            ln -sfn $RELEASES_DIR/\$PREVIOUS $CURRENT_SYM
            rm -rf $PUBLIC_HTML
            ln -sfn $CURRENT_SYM/public $PUBLIC_HTML
            echo "✅ Rollback to \$PREVIOUS complete."
          else
            echo "❌ No previous release found to rollback to!"
          fi
EOF
        exit 1
    fi

    echo -e "${GREEN}✅ Auth Contract Check Passed!${NC}"
    # Cleanup old releases (keep 5)
    ssh -p $PORT $SERVER "cd $RELEASES_DIR && ls -1t | tail -n +6 | xargs rm -rf"
else
    # Rollback if failed
    echo -e "${RED}❌ Health Check Failed (Status: $HEALTH_STATUS).${NC}"
    echo -e "${RED}Response Body: $HEALTH_BODY${NC}"
    echo -e "${YELLOW}🔄 Rolling back...${NC}"
    ssh -p $PORT $SERVER << EOF
      cd $RELEASES_DIR
      # Identify previous release (timestamp sorting)
      PREVIOUS=\$(ls -1t | sed -n '2p')
      if [ ! -z "\$PREVIOUS" ]; then
        ln -sfn $RELEASES_DIR/\$PREVIOUS $CURRENT_SYM
        rm -rf $PUBLIC_HTML
        ln -sfn $CURRENT_SYM/public $PUBLIC_HTML
        echo "✅ Rollback to \$PREVIOUS complete."
      else
        echo "❌ No previous release found to rollback to!"
      fi
EOF
    exit 1
fi
 
echo -e "${GREEN}🎉 Deployment Successful!${NC}"
