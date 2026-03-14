# ROUTING_FIX_REPORT

Date: 2026-03-14

## 1. Root Cause Explanation

### CRITICAL

The application was manually registering `routes/api.php` inside a custom `using:` callback in `bootstrap/app.php` with an environment-based prefix:

```php
Route::middleware('api')
    ->prefix(trim(env('APP_ENV')) === 'production' ? '' : 'api')
    ->group(base_path('routes/api.php'));
```

This is fragile because production is deployed under a webserver subdirectory `/api`:

- the webserver already contributes the public `/api` path
- Laravel should therefore expose internal API routes as `/login`, `/me`, etc.
- if Laravel also internally prefixes API routes with `api`, the public result becomes `/api/api/*`

Laravel 12 already supports `apiPrefix` in `withRouting(...)`. The safe fix is to let the framework register `routes/api.php` with `apiPrefix: ''`.

## 2. Deployment Mount Point

### HIGH

From `/deploy.sh`:

- frontend is copied to `public/app`
- Laravel public files are copied to `public/api`

That confirms the Laravel app is mounted under `/api` externally.

Therefore internal Laravel routes must be:

- `login`
- `me`

so the public endpoints become:

- `/api/login`
- `/api/me`

## 3. File Modified

### Files changed for this routing fix

- `/Users/apple/Downloads/backup_crm_stable_zip_compressed/api/bootstrap/app.php`

### Change made

Replaced the manual API route registration with Laravel’s supported routing config:

```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',
    commands: __DIR__.'/../routes/console.php',
    channels: __DIR__.'/../routes/channels.php',
    health: '/up',
    apiPrefix: '',
    then: function () {
        // custom root / health / status / metrics / monitor / system routes
    },
)
```

This preserves all controllers, middleware, and auth logic. It only removes the duplicated internal API prefix.

## 4. Route Table Before Fix

Command:

```bash
php artisan route:list | grep api
```

Raw output excerpt:

```text
  POST            api/login generated::BDBHoKGe4qbiDg3O › AuthController@login
  GET|HEAD        api/me ..... generated::5TgxzBXkBfjYSYm3 › AuthController@me
  GET|POST|HEAD   api/broadcasting/auth generated::HRo1nfla13ogFsoC › Illumin…
```

Command:

```bash
php artisan route:list --json | jq '.[] | select(.uri | test("login|me"))'
```

Raw output excerpt:

```json
{
  "domain": null,
  "method": "POST",
  "uri": "api/login",
  "name": "generated::BDBHoKGe4qbiDg3O",
  "action": "App\\Http\\Controllers\\AuthController@login",
  "middleware": [
    "api"
  ]
}
{
  "domain": null,
  "method": "GET|HEAD",
  "uri": "api/me",
  "name": "generated::5TgxzBXkBfjYSYm3",
  "action": "App\\Http\\Controllers\\AuthController@me",
  "middleware": [
    "api",
    "App\\Http\\Middleware\\Authenticate:sanctum",
    "App\\Http\\Middleware\\ResolveTenantContext",
    "App\\Http\\Middleware\\RequireSpecialtyForAdmin"
  ]
}
```

## 5. Cache Rebuild Outputs

Command:

```bash
php artisan optimize:clear
```

Output:

```text

   INFO  Clearing cached bootstrap files.  

  config ......................................................... 2.27ms DONE
  cache ......................................................... 13.29ms DONE
  compiled ....................................................... 0.96ms DONE
  events ......................................................... 0.46ms DONE
  routes ......................................................... 0.61ms DONE
  views .......................................................... 3.49ms DONE
```

Command:

```bash
php artisan config:clear
```

Output:

```text

   INFO  Configuration cache cleared successfully.  
```

Command:

```bash
php artisan route:clear
```

Output:

```text

   INFO  Route cache cleared successfully.  
```

Command:

```bash
php artisan view:clear
```

Output:

```text

   INFO  Compiled views cleared successfully.  
```

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

## 6. Route Table After Fix

Command:

```bash
php artisan route:list | grep login
```

Output:

```text
  POST            login ................................. AuthController@login
```

Command:

```bash
php artisan route:list | grep me
```

Output excerpt:

```text
  GET|HEAD        me ....................................... AuthController@me
  GET|HEAD        staff/me ................................ StaffController@me
  GET|HEAD        subscription/me ..... Doctor\DoctorSubscriptionController@me
```

Command:

```bash
php artisan route:list | grep api
```

Output:

```text
  GET|HEAD        horizon/api/batches horizon.jobs-batches.index › Laravel\Ho…
  POST            horizon/api/batches/retry/{id} horizon.jobs-batches.retry  …
  GET|HEAD        horizon/api/batches/{id} horizon.jobs-batches.show › Larave…
  GET|HEAD        horizon/api/jobs/completed horizon.completed-jobs.index › L…
  GET|HEAD        horizon/api/jobs/failed horizon.failed-jobs.index › Laravel…
  GET|HEAD        horizon/api/jobs/failed/{id} horizon.failed-jobs.show › Lar…
  GET|HEAD        horizon/api/jobs/pending horizon.pending-jobs.index › Larav…
  POST            horizon/api/jobs/retry/{id} horizon.retry-jobs.show › Larav…
  GET|HEAD        horizon/api/jobs/silenced horizon.silenced-jobs.index › Lar…
  GET|HEAD        horizon/api/jobs/{id} horizon.jobs.show › Laravel\Horizon  …
  GET|HEAD        horizon/api/masters horizon.masters.index › Laravel\Horizon…
  GET|HEAD        horizon/api/metrics/jobs horizon.jobs-metrics.index › Larav…
  GET|HEAD        horizon/api/metrics/jobs/{id} horizon.jobs-metrics.show › L…
  GET|HEAD        horizon/api/metrics/queues horizon.queues-metrics.index › L…
  GET|HEAD        horizon/api/metrics/queues/{id} horizon.queues-metrics.show…
  GET|HEAD        horizon/api/monitoring horizon.monitoring.index › Laravel\H…
  POST            horizon/api/monitoring horizon.monitoring.store › Laravel\H…
  GET|HEAD        horizon/api/monitoring/{tag} horizon.monitoring-tag.paginat…
  DELETE          horizon/api/monitoring/{tag} horizon.monitoring-tag.destroy…
  GET|HEAD        horizon/api/stats horizon.stats.index › Laravel\Horizon › D…
  GET|HEAD        horizon/api/workload horizon.workload.index › Laravel\Horiz…
```

There is no internal `api/login` or `api/me` anymore.

## 7. HTTP Verification Results

### Local internal routes

Command:

```bash
curl -s -X POST -H 'Accept: application/json' -D - http://127.0.0.1:8000/login | sed -n '1,80p'
```

Output:

```text
HTTP/1.1 422 Unprocessable Content
Host: 127.0.0.1:8000
Connection: close
X-Powered-By: PHP/8.5.1
Cache-Control: no-cache, private
Date: Sat, 14 Mar 2026 10:14:48 GMT
Content-Type: application/json

{"message":"The email field is required. (and 1 more error)","errors":{"email":["The email field is required."],"password":["The password field is required."]}}
```

Command:

```bash
curl -s -H 'Accept: application/json' -D - http://127.0.0.1:8000/me | sed -n '1,80p'
```

Output:

```text
HTTP/1.1 401 Unauthorized
Host: 127.0.0.1:8000
Connection: close
X-Powered-By: PHP/8.5.1
Cache-Control: no-cache, private
Date: Sat, 14 Mar 2026 10:14:48 GMT
Content-Type: application/json

{"message":"Unauthenticated."}
```

### Incorrect prefixed local routes

Command:

```bash
curl -s -X POST -H 'Accept: application/json' -D - http://127.0.0.1:8000/api/login | sed -n '1,80p'
```

Output:

```text
HTTP/1.0 404 Not Found
Host: 127.0.0.1:8000
Connection: close
X-Powered-By: PHP/8.5.1
Cache-Control: no-cache, private
Date: Sat, 14 Mar 2026 10:14:48 GMT
Content-Type: application/json
Vary: Origin

{
    "message": "The route api/login could not be found.",
    "exception": "Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException",
    "file": "/Users/apple/Downloads/backup_crm_stable_zip_compressed/api/vendor/laravel/framework/src/Illuminate/Routing/AbstractRouteCollection.php",
    "line": 45,
```

Command:

```bash
curl -s -H 'Accept: application/json' -D - http://127.0.0.1:8000/api/me | sed -n '1,80p'
```

Output:

```text
HTTP/1.0 404 Not Found
Host: 127.0.0.1:8000
Connection: close
X-Powered-By: PHP/8.5.1
Cache-Control: no-cache, private
Date: Sat, 14 Mar 2026 10:14:48 GMT
Content-Type: application/json
Vary: Origin

{
    "message": "The route api/me could not be found.",
    "exception": "Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException",
    "file": "/Users/apple/Downloads/backup_crm_stable_zip_compressed/api/vendor/laravel/framework/src/Illuminate/Routing/AbstractRouteCollection.php",
    "line": 45,
```

### Interpretation

This is the correct internal behavior after the fix:

- local root app exposes `/login` and `/me`
- if the same app is mounted by the webserver under `/api`, the public endpoints become `/api/login` and `/api/me`
- `/api/api/login` and `/api/api/me` cannot exist unless the app is double-prefixed again

## 8. Production Compatibility Check

### MEDIUM

This source-level routing fix is production-compatible for a subdirectory-mounted Laravel app.

Expected public production endpoints after deploy:

- `POST /api/login`
- `GET /api/me`

Expected non-existent endpoints after deploy:

- `/api/api/login`
- `/api/api/me`

Current live production will not change until this code is deployed and caches are rebuilt on the server.

## 9. Deployment Risk Analysis

### CRITICAL

- If the server still deploys an older cached route table, public routing remains broken.

### HIGH

- Any custom route registration that manually prefixes `routes/api.php` can reintroduce the bug.

### MEDIUM

- Local development now exposes backend routes at `/login` and `/me`, which is correct for a root-mounted local server but may require corresponding local frontend base URL alignment.

### LOW

- Horizon still uses internal `horizon/api/*` routes; this is unrelated to the application API prefix bug.

## Final Result

The Laravel app now registers internal API routes without the `api/` prefix. That is the correct configuration for an app mounted externally under `/api`.

Final intended public production endpoints:

- `POST /api/login`
- `GET /api/me`

Endpoints that should never exist after deploy:

- `/api/api/login`
- `/api/api/me`
