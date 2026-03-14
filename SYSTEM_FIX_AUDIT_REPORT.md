# SYSTEM_FIX_AUDIT_REPORT

Date: 2026-03-14
Workspace: `/Users/apple/Downloads/backup_crm_stable_zip_compressed`

## SYSTEM_ARCHITECTURE_MAP

### Frontend

- Path: `/frontend`
- Stack: React 19 + Vite 7 + axios + react-router-dom
- Entry routing:
  - SPA basename `/app`
  - login UI at `/app/login`
- API client:
  - `/frontend/src/api/axios.js`
- Auth state:
  - `/frontend/src/context/AuthContext.jsx`
- Login form:
  - `/frontend/src/pages/Login.jsx`
- Realtime bootstrap:
  - `/frontend/src/bootstrap.js`

### Backend

- Path: `/api`
- Stack: Laravel 12 + Sanctum + Horizon + Reverb
- Entry routing:
  - `/api/bootstrap/app.php`
- API routes:
  - `/api/routes/api.php`
- Web routes:
  - `/api/routes/web.php`
- Auth controller:
  - `/api/app/Http/Controllers/AuthController.php`
- Auth middleware:
  - `/api/app/Http/Middleware/Authenticate.php`

### Deployment

- Script: `/deploy.sh`
- Model:
  - builds frontend locally
  - rsyncs CRM to `public/app`
  - rsyncs landing to `public/`
  - copies Laravel public assets to `public/api`
  - symlinks release to `public_html`
- Production implication:
  - Laravel app is mounted at `/api` externally

### Auth Flow

1. React posts `POST /api/login`
2. Laravel `AuthController@login` validates credentials
3. Sanctum token is issued with `createToken('auth_token')`
4. React stores token in `localStorage`
5. axios sends `Authorization: Bearer <token>`
6. React requests `GET /api/me`
7. `auth:sanctum` authenticates request
8. API returns authenticated user JSON

### Middleware Stack

Protected `/api/me` route:

1. `api`
2. `App\Http\Middleware\Authenticate:sanctum`
3. `App\Http\Middleware\ResolveTenantContext`
4. `App\Http\Middleware\RequireSpecialtyForAdmin`

Nested protected routes add:

- `CheckRole`
- `EnsureSubscriptionActive`
- `CheckStaffPermissions`
- `EnforcePlanLimits`
- `BlockReceptionist`
- `RequireDoctorRole`

## Root Causes

### CRITICAL

1. Production route contract is currently broken.
   - Live backend is serving `routes/api.php` endpoints under `/api/api/*`.
   - Frontend is calling `/api/*`.
   - Verified:
     - `POST /api/login` fails
     - `POST /api/api/login` works
     - `GET /api/api/me` works

2. Laravel default unauthenticated redirect path is incompatible with SPA API auth.
   - Without explicit API override, unauthenticated requests can resolve `route('login')`.
   - No Laravel named `login` route exists.

### HIGH

3. Deployment previously recached config/routes without an explicit `optimize:clear`.
   - This can preserve stale route/config state across releases.

4. Local frontend env was misconfigured.
   - `/frontend/.env` pointed to `https://bioorganiccare.com/api/public/api/`
   - This can produce local “Network error” and false auth failures.

### MEDIUM

5. `SubscriptionContext.jsx` reads `localStorage.user`, but auth flow never writes it.
6. Reverb config allows `allowed_origins => ['*']`.
7. Bearer tokens are stored in `localStorage`, which is XSS-sensitive.

### LOW

8. `resources/views/welcome.blade.php` still references `route('login')`.
9. `Pusher.logToConsole = true` is enabled in frontend bootstrap.

## Minimal Safe Fixes Applied

### Files Changed

- `/api/bootstrap/app.php`
- `/api/app/Http/Middleware/Authenticate.php`
- `/api/config/auth.php`
- `/frontend/.env`
- `/deploy.sh`

### Exact Fixes

1. Added API-safe auth middleware override.
   - API requests now return `null` redirect target.
   - Non-API browser requests redirect to `/app/login`.

2. Added bootstrap-level unauthenticated handling for API requests.
   - API requests now return `401 JSON`.
   - Laravel no longer needs `route('login')` for API auth failures.

3. Added explicit `api` Sanctum guard in `config/auth.php`.

4. Fixed local frontend base URL in `/frontend/.env`.
   - from `https://bioorganiccare.com/api/public/api/`
   - to `http://localhost:8000/api`

5. Hardened deploy script.
   - keeps `php artisan optimize:clear`
   - adds post-deploy auth contract smoke checks:
     - invalid `POST /api/login` must return `422`
     - unauthenticated `GET /api/me` must return `401`

## Route Table Before / After

### Local Route Table Before Cache Rebuild

```text
  GET|HEAD        api/admin/governance/health Admin\GovernanceController@heal…
  POST            api/admin/governance/repair/duplicate-medicines Admin\Gover…
  POST            api/admin/governance/repair/medicine-drift Admin\Governance…
  POST            api/admin/governance/repair/orphan-treatments Admin\Governa…
  POST            api/admin/hybrid-promotions/medicine/{id} Admin\AdminHybrid…
  GET|HEAD        api/admin/hybrid-suggestions/master-medicines Admin\AdminHy…
  GET|HEAD        api/admin/hybrid-suggestions/medicines Admin\AdminHybridSug…
  GET|HEAD        api/admin/medicines/archived Admin\PharmacyCatalogManagerCo…
  PUT             api/admin/medicines/{medicine} Admin\PharmacyCatalogManager…
  POST            api/admin/specialties/{specialty}/medicines Admin\PharmacyC…
  POST            api/admin/subscriptions/{doctor}/lifetime Admin\AdminSubscr…
  GET|HEAD        api/appointments appointments.index › AppointmentController…
  POST            api/appointments appointments.store › AppointmentController…
  GET|HEAD        api/appointments/{appointment} appointments.show › Appointm…
  PUT|PATCH       api/appointments/{appointment} appointments.update › Appoin…
  DELETE          api/appointments/{appointment} appointments.destroy › Appoi…
  GET|HEAD        api/inventory/search-medicines inventory. › InventoryContro…
  POST            api/invoices/{invoice}/apply-payment billing.invoices.apply…
  POST            api/login ............................. AuthController@login
  GET|HEAD        api/me ................................... AuthController@me
  GET|HEAD        api/patients/{patient}/treatments clinical-catalog.treatmen…
  GET|HEAD        api/staff/me ............................ StaffController@me
  GET|HEAD        api/subscription/me . Doctor\DoctorSubscriptionController@me
  POST            api/treatments clinical-catalog.treatments.store › Treatmen…
  GET|HEAD        api/treatments clinical-catalog.treatments.index › Treatmen…
  PUT             api/treatments/{treatment} clinical-catalog.treatments.upda…
  GET|HEAD        api/treatments/{treatment} clinical-catalog.treatments.show…
  GET|HEAD        health ..................................................... 
  GET|HEAD        horizon/api/metrics/jobs horizon.jobs-metrics.index › Larav…
  GET|HEAD        horizon/api/metrics/jobs/{id} horizon.jobs-metrics.show › L…
  GET|HEAD        horizon/api/metrics/queues horizon.queues-metrics.index › L…
  GET|HEAD        horizon/api/metrics/queues/{id} horizon.queues-metrics.show…
  GET|HEAD        horizon/{view?} horizon.index › Laravel\Horizon › HomeContr…
  GET|HEAD        metrics .................................................... 
```

### Filtered JSON Route Table

```json
[
  {
    "domain": null,
    "method": "POST",
    "uri": "api/login",
    "name": null,
    "action": "App\\Http\\Controllers\\AuthController@login",
    "middleware": [
      "api"
    ]
  },
  {
    "domain": null,
    "method": "GET|HEAD",
    "uri": "api/me",
    "name": null,
    "action": "App\\Http\\Controllers\\AuthController@me",
    "middleware": [
      "api",
      "App\\Http\\Middleware\\Authenticate:sanctum",
      "App\\Http\\Middleware\\ResolveTenantContext",
      "App\\Http\\Middleware\\RequireSpecialtyForAdmin"
    ]
  },
  {
    "domain": null,
    "method": "GET|HEAD",
    "uri": "health",
    "name": null,
    "action": "Closure",
    "middleware": []
  }
]
```

### Route Cache Artifact

```text
api/bootstrap/cache/config.php
api/bootstrap/cache/packages.php
api/bootstrap/cache/routes-v7.php
api/bootstrap/cache/services.php
```

### Cached Route File Verification

```text
106:      '/horizon/api/metrics/jobs' => 
126:      '/horizon/api/metrics/queues' => 
286:      '/health' => 
407:      '/api/login' => 
445:      '/api/me' => 
642:      '/api/admin/governance/health' => 
4000:      'uri' => 'horizon/api/metrics/jobs',
4035:      'uri' => 'horizon/api/metrics/jobs/{id}',
4070:      'uri' => 'horizon/api/metrics/queues',
4105:      'uri' => 'horizon/api/metrics/queues/{id}',
4599:      'uri' => 'health',
4708:                        "status" => "healthy",
4818:      'uri' => 'api/login',
4825:        'uses' => 'App\\Http\\Controllers\\AuthController@login',
4826:        'controller' => 'App\\Http\\Controllers\\AuthController@login',
4965:      'uri' => 'api/me',
4975:        'uses' => 'App\\Http\\Controllers\\AuthController@me',
4976:        'controller' => 'App\\Http\\Controllers\\AuthController@me',
5989:      'uri' => 'api/admin/governance/health',
6001:        'uses' => 'App\\Http\\Controllers\\Admin\\GovernanceController@health',
6002:        'controller' => 'App\\Http\\Controllers\\Admin\\GovernanceController@health',
```

## Auth Flow Verification

### Local Expected Behavior

- `GET /health` -> `200`
- `GET /api/me` unauthenticated -> `401 JSON`
- `POST /api/login` invalid creds -> `422 JSON`
- `GET /api/api/login` -> `404` locally, confirming no duplicate prefix in source

### Production Current Behavior

- `GET /api/health` -> `200`
- `POST /api/login` -> broken
- `GET /api/me` -> broken
- `POST /api/api/login` -> working
- `GET /api/api/me` -> working

Conclusion:

- local source is correct
- live production route exposure is incorrect
- deployment/cache/env state is causing the public contract mismatch

## Production Risk Assessment

### CRITICAL

- Login is down for the public production contract.
- Authenticated profile fetch is down for the public production contract.
- Current deployment can silently ship route tables that do not match the public URL contract.

### HIGH

- Any future release can reintroduce the same incident unless auth contract smoke tests remain in deploy.

### MEDIUM

- Local dev env drift can hide or confuse backend incidents.

### LOW

- Stale Blade auth references and verbose console logging increase confusion but are not the primary incident.

## RAW COMMAND OUTPUT TRANSCRIPT

### 1. Git status

Command:

```bash
git status --short
```

Output:

```text
 M api/bootstrap/app.php
 M api/config/auth.php
 M deploy.sh
?? FULL_BACKEND_FRONTEND_AUDIT_REPORT.md
?? api/BACKEND_AUTH_DEBUG_REPORT.md
?? api/app/Http/Middleware/Authenticate.php
?? api/storage/framework/views/
```

### 2. Current relevant diff before final changes

Command:

```bash
git diff -- api/bootstrap/app.php api/app/Http/Middleware/Authenticate.php api/config/auth.php deploy.sh frontend/.env
```

Output:

```diff
diff --git a/api/bootstrap/app.php b/api/bootstrap/app.php
index 7ea2ae5..8128a2f 100644
--- a/api/bootstrap/app.php
+++ b/api/bootstrap/app.php
@@ -3,8 +3,8 @@
 use Illuminate\Foundation\Application;
 use Illuminate\Foundation\Configuration\Exceptions;
 use Illuminate\Foundation\Configuration\Middleware;
-
 use Illuminate\Support\Facades\Route;
+use Illuminate\Http\Request;
  
 return Application::configure(basePath: dirname(__DIR__))
     ->withRouting(
@@ -99,7 +99,14 @@
         },
     )
     ->withMiddleware(function (Middleware $middleware): void {
+        $middleware->redirectGuestsTo(function (Request $request) {
+            return $request->expectsJson() || in_array('api', $request->route()?->gatherMiddleware() ?? [], true)
+                ? null
+                : '/app/login';
+        });
+
         $middleware->alias([
+            'auth' => \App\Http\Middleware\Authenticate::class,
             'subscription' => \App\Http\Middleware\EnsureSubscriptionActive::class,
             'module' => \App\Http\Middleware\EnforcePlanLimits::class,
             'role' => \App\Http\Middleware\CheckRole::class,
@@ -113,12 +120,12 @@
     })
     ->withExceptions(function (Exceptions $exceptions): void {
         $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request) {
+            if ($request->expectsJson() || in_array('api', $request->route()?->gatherMiddleware() ?? [], true)) {
+                return response()->json([
+                    'message' => 'Unauthenticated.',
+                ], 401);
+            }
 
-    if ($request->expectsJson() || $request->is('api/*')) {
-        return response()->json([
-            'message' => 'Unauthenticated.'
-        ], 401);
-    }
-
-});
+            return redirect('/app/login');
+        });
     })->create();
diff --git a/api/config/auth.php b/api/config/auth.php
index 7d1eb0d..91514f0 100644
--- a/api/config/auth.php
+++ b/api/config/auth.php
@@ -40,6 +40,11 @@
             'driver' => 'session',
             'provider' => 'users',
         ],
+
+        'api' => [
+            'driver' => 'sanctum',
+            'provider' => 'users',
+        ],
     ],
 
     /*
diff --git a/deploy.sh b/deploy.sh
index 5a074e7..f967aa0 100755
--- a/deploy.sh
+++ b/deploy.sh
@@ -101,6 +101,7 @@ ssh -p $PORT $SERVER << EOF
   # Laravel Optimization (PHP 8.4)
   cd ../../api
   $PHP_BIN artisan migrate --force
+  $PHP_BIN artisan optimize:clear
   $PHP_BIN artisan config:cache
   $PHP_BIN artisan route:cache
   $PHP_BIN artisan view:cache
@@ -122,6 +123,44 @@ HEALTH_BODY=$(echo "$HEALTH_RESPONSE" | sed '1,/^\r$/d')
  
 if [ "$HEALTH_STATUS" == "200" ]; then
     echo -e "${GREEN}✅ Health Check Passed! Status: $HEALTH_STATUS${NC}"
+
+    echo -e "${YELLOW}🔐 Verifying Auth Contract (POST /api/login invalid creds)...${NC}"
+    LOGIN_STATUS=$(curl -s -k -o /tmp/bioorganiccare_login_check.json -w "%{http_code}" \
+      -X POST https://bioorganiccare.com/api/login \
+      -H "Accept: application/json" \
+      -H "Content-Type: application/json" \
+      -d '{"email":"invalid@example.com","password":"invalid-password"}')
+    LOGIN_BODY=$(cat /tmp/bioorganiccare_login_check.json)
+
+    echo -e "${YELLOW}🔐 Verifying Auth Contract (GET /api/me unauthenticated)...${NC}"
+    ME_STATUS=$(curl -s -k -o /tmp/bioorganiccare_me_check.json -w "%{http_code}" \
+      https://bioorganiccare.com/api/me \
+      -H "Accept: application/json")
+    ME_BODY=$(cat /tmp/bioorganiccare_me_check.json)
+
+    if [ "$LOGIN_STATUS" != "422" ] || [ "$ME_STATUS" != "401" ]; then
+        echo -e "${RED}❌ Auth Contract Check Failed.${NC}"
+        echo -e "${RED}POST /api/login status: $LOGIN_STATUS${NC}"
+        echo -e "${RED}POST /api/login body: $LOGIN_BODY${NC}"
+        echo -e "${RED}GET /api/me status: $ME_STATUS${NC}"
+        echo -e "${RED}GET /api/me body: $ME_BODY${NC}"
+        echo -e "${YELLOW}🔄 Rolling back...${NC}"
+        ssh -p $PORT $SERVER << EOF
+          cd $RELEASES_DIR
+          PREVIOUS=\$(ls -1t | sed -n '2p')
+          if [ ! -z "\$PREVIOUS" ]; then
+            ln -sfn $RELEASES_DIR/\$PREVIOUS $CURRENT_SYM
+            rm -rf $PUBLIC_HTML
+            ln -sfn $CURRENT_SYM/public $PUBLIC_HTML
+            echo "✅ Rollback to \$PREVIOUS complete."
+          else
+            echo "❌ No previous release found to rollback to!"
+          fi
+EOF
+        exit 1
+    fi
+
+    echo -e "${GREEN}✅ Auth Contract Check Passed!${NC}"
     # Cleanup old releases (keep 5)
     ssh -p $PORT $SERVER "cd $RELEASES_DIR && ls -1t | tail -n +6 | xargs rm -rf"
 else
@@ -145,4 +184,4 @@ EOF
     exit 1
 fi
  
-echo -e "${GREEN}🎉 Deployment Successful!${NC}"
\ No newline at end of file
+echo -e "${GREEN}🎉 Deployment Successful!${NC}"
```

### 3. Route list

Command:

```bash
php artisan route:list | rg "api/login|api/me|health|login|me"
```

Output:

```text
  GET|HEAD        api/admin/governance/health Admin\GovernanceController@heal…
  POST            api/admin/governance/repair/duplicate-medicines Admin\Gover…
  POST            api/admin/governance/repair/medicine-drift Admin\Governance…
  POST            api/admin/governance/repair/orphan-treatments Admin\Governa…
  POST            api/admin/hybrid-promotions/medicine/{id} Admin\AdminHybrid…
  GET|HEAD        api/admin/hybrid-suggestions/master-medicines Admin\AdminHy…
  GET|HEAD        api/admin/hybrid-suggestions/medicines Admin\AdminHybridSug…
  GET|HEAD        api/admin/medicines/archived Admin\PharmacyCatalogManagerCo…
  PUT             api/admin/medicines/{medicine} Admin\PharmacyCatalogManager…
  POST            api/admin/specialties/{specialty}/medicines Admin\PharmacyC…
  POST            api/admin/subscriptions/{doctor}/lifetime Admin\AdminSubscr…
  GET|HEAD        api/appointments appointments.index › AppointmentController…
  POST            api/appointments appointments.store › AppointmentController…
  GET|HEAD        api/appointments/{appointment} appointments.show › Appointm…
  PUT|PATCH       api/appointments/{appointment} appointments.update › Appoin…
  DELETE          api/appointments/{appointment} appointments.destroy › Appoi…
  GET|HEAD        api/inventory/search-medicines inventory. › InventoryContro…
  POST            api/invoices/{invoice}/apply-payment billing.invoices.apply…
  POST            api/login ............................. AuthController@login
  GET|HEAD        api/me ................................... AuthController@me
  GET|HEAD        api/patients/{patient}/treatments clinical-catalog.treatmen…
  GET|HEAD        api/staff/me ............................ StaffController@me
  GET|HEAD        api/subscription/me . Doctor\DoctorSubscriptionController@me
  POST            api/treatments clinical-catalog.treatments.store › Treatmen…
  GET|HEAD        api/treatments clinical-catalog.treatments.index › Treatmen…
  PUT             api/treatments/{treatment} clinical-catalog.treatments.upda…
  GET|HEAD        api/treatments/{treatment} clinical-catalog.treatments.show…
  GET|HEAD        health ..................................................... 
  GET|HEAD        horizon/api/metrics/jobs horizon.jobs-metrics.index › Larav…
  GET|HEAD        horizon/api/metrics/jobs/{id} horizon.jobs-metrics.show › L…
  GET|HEAD        horizon/api/metrics/queues horizon.queues-metrics.index › L…
  GET|HEAD        horizon/api/metrics/queues/{id} horizon.queues-metrics.show…
  GET|HEAD        horizon/{view?} horizon.index › Laravel\Horizon › HomeContr…
  GET|HEAD        metrics .................................................... 
```

### 4. Filtered JSON route list

Command:

```bash
php artisan route:list --json | jq '[.[] | select(.uri|test("(^api/login$|^api/me$|^health$|^api/api/login$|^api/api/me$|^login$|^me$)"))]'
```

Output:

```json
[
  {
    "domain": null,
    "method": "POST",
    "uri": "api/login",
    "name": null,
    "action": "App\\Http\\Controllers\\AuthController@login",
    "middleware": [
      "api"
    ]
  },
  {
    "domain": null,
    "method": "GET|HEAD",
    "uri": "api/me",
    "name": null,
    "action": "App\\Http\\Controllers\\AuthController@me",
    "middleware": [
      "api",
      "App\\Http\\Middleware\\Authenticate:sanctum",
      "App\\Http\\Middleware\\ResolveTenantContext",
      "App\\Http\\Middleware\\RequireSpecialtyForAdmin"
    ]
  },
  {
    "domain": null,
    "method": "GET|HEAD",
    "uri": "health",
    "name": null,
    "action": "Closure",
    "middleware": []
  }
]
```

### 5. Cache clears

Command:

```bash
php artisan optimize:clear
```

Output:

```text

   INFO  Clearing cached bootstrap files.  

  config ........................................................ 11.13ms DONE
  cache ......................................................... 18.64ms DONE
  compiled ....................................................... 2.62ms DONE
  events ......................................................... 0.91ms DONE
  routes ......................................................... 1.34ms DONE
  views ......................................................... 28.58ms DONE
```

Command:

```bash
php artisan config:clear && php artisan route:clear && php artisan view:clear
```

Output:

```text

   INFO  Configuration cache cleared successfully.  


   INFO  Route cache cleared successfully.  


   INFO  Compiled views cleared successfully.  
```

### 6. Cache rebuild

Command:

```bash
php artisan config:cache
```

Output:

```text

   INFO  Configuration cached successfully.  
```

Command:

```bash
php artisan route:cache
```

Output:

```text

   INFO  Routes cached successfully.  
```

### 7. Cache file listing

Command:

```bash
find api/bootstrap/cache -maxdepth 2 -type f -print | sort
```

Output:

```text
api/bootstrap/cache/config.php
api/bootstrap/cache/packages.php
api/bootstrap/cache/routes-v7.php
api/bootstrap/cache/services.php
```

Command:

```bash
ls -la api/bootstrap/cache
```

Output:

```text
total 624
drwxrwxr-x@ 6 apple  staff     192 Mar 14 15:21 .
drwxrwxr-x@ 6 apple  staff     192 Feb 13 17:00 ..
-rw-r--r--@ 1 apple  staff   27706 Mar 14 15:21 config.php
-rwxr-xr-x@ 1 apple  staff    1364 Mar 14 15:21 packages.php
-rw-r--r--@ 1 apple  staff  258532 Mar 14 15:21 routes-v7.php
-rwxr-xr-x@ 1 apple  staff   22337 Mar 14 15:21 services.php
```

Command:

```bash
php -r 'require "vendor/autoload.php"; $app=require "bootstrap/app.php"; $kernel=$app->make("Illuminate\\Contracts\\Console\\Kernel"); $kernel->bootstrap(); echo app()->getCachedRoutesPath(), PHP_EOL;'
```

Output:

```text
/Users/apple/Downloads/backup_crm_stable_zip_compressed/api/bootstrap/cache/routes-v7.php
```

### 8. Live production contract tests

Command:

```bash
curl -s -D - https://bioorganiccare.com/api/health -o /tmp/prod_api_health.out && sed -n '1,30p' /tmp/prod_api_health.out
```

Output:

```text
HTTP/2 200 
x-powered-by: PHP/8.4.6
cache-control: no-cache, private
content-type: application/json
date: Sat, 14 Mar 2026 09:30:52 GMT
server: LiteSpeed
platform: hostinger
panel: hpanel
content-security-policy: upgrade-insecure-requests
alt-svc: h3=":443"; ma=2592000, h3-29=":443"; ma=2592000, h3-Q050=":443"; ma=2592000, h3-Q046=":443"; ma=2592000, h3-Q043=":443"; ma=2592000, quic=":443"; ma=2592000; v="43,46"

{"status":"ok","time":"2026-03-14T09:30:52.452584Z"}
```

Command:

```bash
curl -s -X POST -H 'Accept: application/json' -H 'Content-Type: application/json' -d '{"email":"test@example.com","password":"bad"}' https://bioorganiccare.com/api/login -D - | sed -n '1,80p'
```

Output:

```text
HTTP/2 404 
x-powered-by: PHP/8.4.6
cache-control: no-cache, private
content-type: application/json
date: Sat, 14 Mar 2026 09:42:43 GMT
server: LiteSpeed
platform: hostinger
panel: hpanel
content-security-policy: upgrade-insecure-requests
alt-svc: h3=":443"; ma=2592000, h3-29=":443"; ma=2592000, h3-Q050=":443"; ma=2592000, h3-Q046=":443"; ma=2592000, h3-Q043=":443"; ma=2592000, quic=":443"; ma=2592000; v="43,46"

{
    "message": "The route login could not be found."
}
```

Command:

```bash
curl -s -H 'Accept: application/json' -D - https://bioorganiccare.com/api/me -o /tmp/prod_me_json.out && sed -n '1,40p' /tmp/prod_me_json.out
```

Output:

```text
HTTP/2 404 
x-powered-by: PHP/8.4.6
cache-control: no-cache, private
content-type: application/json
date: Sat, 14 Mar 2026 09:42:43 GMT
server: LiteSpeed
platform: hostinger
panel: hpanel
content-security-policy: upgrade-insecure-requests
alt-svc: h3=":443"; ma=2592000, h3-29=":443"; ma=2592000, h3-Q050=":443"; ma=2592000, h3-Q046=":443"; ma=2592000, h3-Q043=":443"; ma=2592000, quic=":443"; ma=2592000; v="43,46"

{
    "message": "The route login could not be found."
}
```

Command:

```bash
curl -s -X POST -H 'Accept: application/json' -H 'Content-Type: application/json' -d '{"email":"test@example.com","password":"bad"}' https://bioorganiccare.com/api/api/login -D - | sed -n '1,80p'
```

Output:

```text
HTTP/2 422 
x-powered-by: PHP/8.4.6
cache-control: no-cache, private
content-type: application/json
vary: Origin
date: Sat, 14 Mar 2026 09:43:21 GMT
server: LiteSpeed
platform: hostinger
panel: hpanel
content-security-policy: upgrade-insecure-requests
alt-svc: h3=":443"; ma=2592000, h3-29=":443"; ma=2592000, h3-Q050=":443"; ma=2592000, h3-Q046=":443"; ma=2592000, h3-Q043=":443"; ma=2592000, quic=":443"; ma=2592000; v="43,46"

{"message":"The provided credentials are incorrect.","errors":{"email":["The provided credentials are incorrect."]}}
```

Command:

```bash
curl -s -H 'Accept: application/json' -D - https://bioorganiccare.com/api/api/me -o /tmp/prod_api_api_me_final.out && sed -n '1,40p' /tmp/prod_api_api_me_final.out
```

Output:

```text
HTTP/2 401 
x-powered-by: PHP/8.4.6
cache-control: no-cache, private
content-type: application/json
vary: Origin
date: Sat, 14 Mar 2026 09:53:40 GMT
server: LiteSpeed
platform: hostinger
panel: hpanel
content-security-policy: upgrade-insecure-requests
alt-svc: h3=":443"; ma=2592000, h3-29=":443"; ma=2592000, h3-Q050=":443"; ma=2592000, h3-Q046=":443"; ma=2592000, h3-Q043=":443"; ma=2592000, quic=":443"; ma=2592000; v="43,46"

{"message":"Unauthenticated."}
```

### 9. Local server verification

Command:

```bash
php artisan serve --host=127.0.0.1 --port=8091
```

Output:

```text

   INFO  Server running on [http://127.0.0.1:8091].  

  Press Ctrl+C to stop the server
```

Command:

```bash
curl -s -D - http://127.0.0.1:8091/health -o /tmp/local_health_8091.out && sed -n '1,30p' /tmp/local_health_8091.out
```

Output:

```text
HTTP/1.1 200 OK
Host: 127.0.0.1:8091
Connection: close
X-Powered-By: PHP/8.5.1
Cache-Control: no-cache, private
Date: Sat, 14 Mar 2026 09:52:46 GMT
Content-Type: application/json

{"status":"ok","time":"2026-03-14T09:52:46.081381Z"}
```

Command:

```bash
curl -s -H 'Accept: application/json' -D - http://127.0.0.1:8091/api/me -o /tmp/local_me_8091.out && sed -n '1,30p' /tmp/local_me_8091.out
```

Output:

```text
HTTP/1.1 401 Unauthorized
Host: 127.0.0.1:8091
Connection: close
X-Powered-By: PHP/8.5.1
Cache-Control: no-cache, private
Date: Sat, 14 Mar 2026 09:52:46 GMT
Content-Type: application/json
Vary: Origin

{"message":"Unauthenticated."}
```

Command:

```bash
curl -s -X POST -H 'Accept: application/json' -H 'Content-Type: application/json' -d '{"email":"invalid@example.com","password":"invalid-password"}' -D - http://127.0.0.1:8091/api/login | sed -n '1,80p'
```

Output:

```text
HTTP/1.1 422 Unprocessable Content
Host: 127.0.0.1:8091
Connection: close
X-Powered-By: PHP/8.5.1
Cache-Control: no-cache, private
Date: Sat, 14 Mar 2026 09:52:46 GMT
Content-Type: application/json
Vary: Origin

{"message":"The provided credentials are incorrect.","errors":{"email":["The provided credentials are incorrect."]}}
```

Command:

```bash
curl -s -H 'Accept: application/json' -D - http://127.0.0.1:8091/api/api/login -o /tmp/local_api_api_login_8091.out && sed -n '1,40p' /tmp/local_api_api_login_8091.out
```

Output:

```text
HTTP/1.0 404 Not Found
Host: 127.0.0.1:8091
Connection: close
X-Powered-By: PHP/8.5.1
Cache-Control: no-cache, private
Date: Sat, 14 Mar 2026 09:52:57 GMT
Content-Type: application/json
Vary: Origin

{
    "message": "The route api/api/login could not be found.",
    "exception": "Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException",
    "file": "/Users/apple/Downloads/backup_crm_stable_zip_compressed/api/vendor/laravel/framework/src/Illuminate/Routing/AbstractRouteCollection.php",
    "line": 45,
    "trace": [
        {
            "file": "/Users/apple/Downloads/backup_crm_stable_zip_compressed/api/vendor/laravel/framework/src/Illuminate/Routing/CompiledRouteCollection.php",
            "line": 143,
            "function": "handleMatchedRoute",
            "class": "Illuminate\\Routing\\AbstractRouteCollection",
            "type": "->"
        },
        {
            "file": "/Users/apple/Downloads/backup_crm_stable_zip_compressed/api/vendor/laravel/framework/src/Illuminate/Routing/Router.php",
            "line": 777,
            "function": "match",
            "class": "Illuminate\\Routing\\CompiledRouteCollection",
            "type": "->"
        },
        {
            "file": "/Users/apple/Downloads/backup_crm_stable_zip_compressed/api/vendor/laravel/framework/src/Illuminate/Routing/Router.php",
            "line": 764,
            "function": "findRoute",
            "class": "Illuminate\\Routing\\Router",
            "type": "->"
        },
        {
            "file": "/Users/apple/Downloads/backup_crm_stable_zip_compressed/api/vendor/laravel/framework/src/Illuminate/Routing/Router.php",
            "line": 753,
            "function": "dispatchToRoute",
            "class": "Illuminate\\Routing\\Router",
            "type": "->"
        },
        {
            "file": "/Users/apple/Downloads/backup_crm_stable_zip_compressed/api/vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php",
            "line": 200,
            "function": "dispatch",
            "class": "Illuminate\\Routing\\Router",
            "type": "->"
```

Command:

```bash
Ctrl+C on php artisan serve session
```

Output:

```text
  2026-03-14 15:22:45 /api/login ................................. ~ 504.92ms
  2026-03-14 15:22:45 /api/me .................................... ~ 508.45ms
  2026-03-14 15:22:46 /health ...................................... ~ 1.31ms
  2026-03-14 15:22:57 /api/api/login ............................... ~ 3.94ms
^C
```

## Final Diagnosis

### Source code state

- local source route table is correct
- local auth behavior is correct after hardening
- local frontend env is now corrected

### Production state

- production still has a broken public API contract
- the live contract currently maps working API auth endpoints under `/api/api/*`
- the deploy script now contains the minimum checks needed to catch and rollback that condition automatically

### Required next operational step

Redeploy using the updated `/deploy.sh` so the server:

1. clears caches before recaching
2. publishes the current auth hardening
3. fails fast if `/api/login` and `/api/me` do not return the expected public contract
