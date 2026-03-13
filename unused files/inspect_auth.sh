#!/bin/bash

echo "================ AUTH CONTROLLER =================="
sed -n '1,200p' backend/app/Http/Controllers/AuthController.php 2>/dev/null

echo "================ API ROUTE MIDDLEWARE BLOCK ======="
sed -n '1,200p' backend/routes/api.php

echo "================ AUTH CONFIG ======================="
sed -n '1,200p' backend/config/auth.php

echo "================ SANCTUM CONFIG ===================="
sed -n '1,200p' backend/config/sanctum.php 2>/dev/null

echo "================ USER MODEL ========================"
sed -n '1,200p' backend/app/Models/User.php 2>/dev/null
