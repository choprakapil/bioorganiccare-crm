#!/bin/bash

echo "================ BACKEND STRUCTURE ================"
# Try tree, fallback to find
if command -v tree &> /dev/null; then
    tree -L 3 backend
else
    find backend -maxdepth 3 -not -path '*/.*' -not -path '*/vendor*' -print
fi

echo "================ CONTROLLERS ======================"
find backend/app/Http/Controllers -type f -print

echo "================ MODELS ==========================="
find backend/app/Models -type f -print

echo "================ MIDDLEWARE ======================="
find backend/app/Http/Middleware -type f -print

echo "================ ROUTE FILES ======================"
echo "--- api.php ---"
cat backend/routes/api.php
echo "--- web.php ---"
cat backend/routes/web.php 2>/dev/null

echo "================ FRONTEND STRUCTURE ==============="
if command -v tree &> /dev/null; then
    tree -L 3 frontend
else
    find frontend -maxdepth 3 -not -path '*/.*' -not -path '*/node_modules*' -print
fi

echo "================ FRONTEND API USAGE (Top 50) ==============="
grep -rE "axios|fetch|api/" frontend/src 2>/dev/null | head -n 50

echo "================ AUTH CONFIG ======================"
grep -rE "Sanctum|auth|middleware" backend/config 2>/dev/null | head -n 50

echo "================ CORS CONFIG ======================"
cat backend/config/cors.php 2>/dev/null

echo "================ APP CONFIG (First 120 lines) ======================="
cat backend/config/app.php 2>/dev/null | head -n 120
