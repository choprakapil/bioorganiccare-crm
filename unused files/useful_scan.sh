#!/bin/bash

echo "=============================="
echo "===== FULL PROJECT SCAN ====="
echo "=============================="

# Exclusions for find (standard ignored directories)
# We exclude them from traversal
FIND_EXCLUDES="-type d \( -name node_modules -o -name vendor -o -name .git -o -name .next -o -name build -o -name dist -o -name storage \) -prune -o"

# Excluions for grep
GREP_EXCLUDES="--exclude-dir={node_modules,vendor,.git,.next,build,dist,storage}"

# 1️⃣ FULL DIRECTORY TREE (COMPLETE - Filtered)
echo "===== COMPLETE DIRECTORY TREE (Filtered) ====="
find . $FIND_EXCLUDES -print

# 2️⃣ FILE TYPE SUMMARY
echo "===== FILE TYPE SUMMARY ====="
find . $FIND_EXCLUDES -type f -print | sed 's/.*\.//' | sort | uniq -c | sort -nr

# 3️⃣ ALL CONFIGURATION FILES (FULL CONTENT)
echo "===== PACKAGE.JSON ====="
cat package.json 2>/dev/null

echo "===== NEXT CONFIG ====="
cat next.config.* 2>/dev/null
cat frontend/next.config.* 2>/dev/null

echo "===== TS/JS CONFIG ====="
cat tsconfig.json 2>/dev/null
cat jsconfig.json 2>/dev/null
cat frontend/tsconfig.json 2>/dev/null
cat frontend/jsconfig.json 2>/dev/null


echo "===== DOCKER CONFIG ====="
cat Dockerfile* 2>/dev/null
cat docker-compose* 2>/dev/null

echo "===== ALL YAML CONFIGS ====="
find . $FIND_EXCLUDES -name "*.yml" -o -name "*.yaml" -exec echo "--- {} ---" \; -exec cat {} \; 2>/dev/null

# 4️⃣ ENVIRONMENT FILE NAMES ONLY
echo "===== ENV FILES (NAMES ONLY) ====="
find . -maxdepth 3 -name ".env*"

# 5️⃣ DATABASE DISCOVERY
echo "===== PRISMA SCHEMA ====="
find . $FIND_EXCLUDES -name "schema.prisma" -exec echo "--- {} ---" \; -exec cat {} \; 2>/dev/null

echo "===== SQL FILES ====="
find . $FIND_EXCLUDES -name "*.sql" -exec echo "--- {} ---" \; -exec cat {} \; 2>/dev/null

# 6️⃣ API & ROUTE DISCOVERY
echo "===== API / ROUTE FILES ====="
find . $FIND_EXCLUDES -type f \( -path "*/api/*" -o -name "*route.*" -o -name "*controller.*" -o -name "*service.*" \) -print

# 7️⃣ COMPONENT & LIB STRUCTURE
echo "===== COMPONENTS STRUCTURE ====="
find . $FIND_EXCLUDES -type d -name "components" -print

echo "===== LIB / UTILS STRUCTURE ====="
find . $FIND_EXCLUDES -type d \( -name "lib" -o -name "utils" \) -print

# 8️⃣ DATABASE CLIENT USAGE SCAN
echo "===== DATABASE USAGE SEARCH ====="
grep -r "prisma\|mongoose\|sequelize\|knex\|mysql\|pg\|supabase" . $GREP_EXCLUDES 2>/dev/null | head -n 50

# 9️⃣ AUTH DETECTION
echo "===== AUTH RELATED FILES ====="
grep -r "auth\|next-auth\|jwt\|session\|passport" . $GREP_EXCLUDES 2>/dev/null | head -n 50

# 🔟 NODE & PACKAGE MANAGER
echo "===== NODE VERSION ====="
node -v

echo "===== NPM VERSION ====="
npm -v

echo "===== LOCK FILE CHECK ====="
ls -la | grep -E "package-lock.json|yarn.lock|pnpm-lock.yaml"
ls -la frontend/ | grep -E "package-lock.json|yarn.lock|pnpm-lock.yaml"
ls -la backend/ | grep -E "package-lock.json|yarn.lock|pnpm-lock.yaml"

echo "=============================="
echo "===== END OF FULL SCAN ====="
echo "=============================="
