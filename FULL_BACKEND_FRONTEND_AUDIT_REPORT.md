# FULL BACKEND FRONTEND AUDIT REPORT

Date: 2026-03-14
Workspace: `/Users/apple/Downloads/backup_crm_stable_zip_compressed`

## Executive Summary

This is a split SPA + API system:

- React CRM served from `/app`
- Laravel backend served from `/api`
- Sanctum used for bearer tokens, not cookie/session SPA auth

Three distinct failures exist:

1. Production route resolution is currently misaligned. Live API routes from `routes/api.php` are exposed under `/api/api/*`, while the frontend calls `/api/*`.
2. Laravel 12’s default unauthenticated fallback still points to `route('login')` unless explicitly overridden for API requests.
3. The frontend local development environment is configured to call `https://bioorganiccare.com/api/public/api/`, which can cause false "Network error" and invalid-auth symptoms during local work.

These issues overlap, which is why symptoms appear inconsistent across environments.

## PROJECT_ARCHITECTURE_MAP

### Backend

Root: `/Users/apple/Downloads/backup_crm_stable_zip_compressed/api`

Key areas:

- `app/Http/Controllers`
  - auth, admin, billing, inventory, patients, settings, notifications
- `app/Http/Middleware`
  - `Authenticate`
  - `ResolveTenantContext`
  - `RequireSpecialtyForAdmin`
  - `CheckRole`
  - `CheckStaffPermissions`
  - `EnsureSubscriptionActive`
  - `EnforcePlanLimits`
  - receptionist/doctor/staff protection middleware
- `app/Models`
  - `User` uses `HasApiTokens`
- `bootstrap/app.php`
  - custom route registration
  - middleware aliasing
  - exception rendering
- `routes/api.php`
  - auth, user profile, admin, patients, finance, billing, settings, notifications
- `routes/web.php`
  - only `/` welcome view
- `public/index.php`
  - environment-aware base path detection for local and Hostinger atomic deployment

### Frontend

Root: `/Users/apple/Downloads/backup_crm_stable_zip_compressed/frontend`

Key areas:

- `src/api/axios.js`
  - shared axios client
  - bearer token injection
  - response interceptor
- `src/context/AuthContext.jsx`
  - login, logout, refreshUser, startup auth hydration
- `src/pages/Login.jsx`
  - login form
- `src/App.jsx`
  - router with `basename="/app"`
  - protected routes
- `src/bootstrap.js`
  - Laravel Echo / Reverb setup

### Deployment Structure

Root deployment script:

- `/Users/apple/Downloads/backup_crm_stable_zip_compressed/deploy.sh`

Production deploy model:

- frontend is built locally and rsynced to `public/app`
- landing is built locally and rsynced to `public/`
- Laravel public assets are copied to `public/api`
- app is symlinked into `public_html`

This means production Laravel is mounted under `/api` at the webserver level.

## 1. Root Causes

### Root Cause A: Production route-cache / route-prefix mismatch

This is the primary live failure.

Source code in `bootstrap/app.php` uses:

```php
Route::middleware('api')
    ->prefix(trim(env('APP_ENV')) === 'production' ? '' : 'api')
    ->group(base_path('routes/api.php'));
```

That means:

- local/internal app routes are `/api/login`, `/api/me`
- production/internal app routes are `/login`, `/me`
- external production URLs still become `/api/login`, `/api/me` because Laravel is mounted inside `/public/api`

Live production verification shows the deployed server is currently behaving as if `routes/api.php` was cached with the local prefix:

- `GET https://bioorganiccare.com/api/up` -> `200`
- `GET https://bioorganiccare.com/api/system` -> `200`
- `GET https://bioorganiccare.com/api/health` -> `200`
- `POST https://bioorganiccare.com/api/login` -> `404 {"message":"The route login could not be found."}`
- `GET https://bioorganiccare.com/api/me` -> `404 {"message":"The route login could not be found."}`
- `POST https://bioorganiccare.com/api/api/login` -> `422` validation JSON
- `GET https://bioorganiccare.com/api/api/me` -> `401 {"message":"Unauthenticated."}`

Conclusion:

- bootstrap-level endpoints are mounted correctly under `/api`
- `routes/api.php` endpoints are currently live under `/api/api/*`
- production route cache is stale or was built from a non-production prefix configuration

### Root Cause B: Laravel default guest redirect fallback

Framework source:

- `vendor/laravel/framework/src/Illuminate/Foundation/Exceptions/Handler.php`

Relevant behavior:

```php
redirect()->guest($exception->redirectTo($request) ?? route('login'))
```

Framework boot default:

- `ApplicationBuilder::withMiddleware()` installs `redirectGuestsTo(fn () => route('login'))`

This project has no named `login` route in Laravel, because login UI is handled by React at `/app/login`.

Without an API-specific override, any unauthenticated request that is not positively classified as JSON will crash on `route('login')`.

### Root Cause C: Local frontend environment misconfiguration

File:

- `/Users/apple/Downloads/backup_crm_stable_zip_compressed/frontend/.env`

Current value:

```env
VITE_API_URL=https://bioorganiccare.com/api/public/api/
```

This is incorrect for both local dev and production parity.

Impact:

- local dev frontend talks to a production-like malformed path
- easy source of "Network error. Please check your connection."
- easy source of confusion during debugging

## 2. Route Resolution Analysis

### Local route table

Local `php artisan route:list` shows:

- `POST api/login`
- `GET|HEAD api/me`
- `POST api/logout`
- no named `login` route
- no `GET login`

### Production path model

Production deploy copies Laravel public assets into `public/api`, so:

- external `/api/*` maps into the Laravel app mounted at that subdirectory
- internal Laravel path prefixing and external URL prefixing are not the same thing

This design is valid, but fragile.

### Fragility

The route path depends on both:

- `APP_ENV`
- where the public front controller is mounted

Once route caching is added, any mismatch between actual env and cached env breaks the public API contract.

## 3. Middleware Chain

### Protected API chain for `/me`

From route list:

1. `api`
2. `App\Http\Middleware\Authenticate:sanctum`
3. `App\Http\Middleware\ResolveTenantContext`
4. `App\Http\Middleware\RequireSpecialtyForAdmin`

Nested route groups then add:

- `CheckRole`
- `EnsureSubscriptionActive`
- `CheckStaffPermissions`
- `EnforcePlanLimits`
- `BlockReceptionist`
- `RequireDoctorRole`

### AuthenticationException chain

Original failure path:

1. request hits protected route without valid auth
2. `auth:sanctum` throws `AuthenticationException`
3. if request is not treated as JSON, Laravel falls back to guest redirect
4. guest redirect resolves `route('login')`
5. route does not exist
6. exception message becomes `The route login could not be found.`

## 4. Authentication Flow Trace

### Intended flow

1. frontend posts `POST /api/login`
2. backend runs `AuthController@login`
3. backend issues Sanctum personal access token
4. frontend stores token in `localStorage`
5. axios attaches `Authorization: Bearer <token>`
6. frontend calls `GET /api/me`
7. `auth:sanctum` authenticates token
8. backend returns user and enabled modules

### Current source implementation

Backend:

- `AuthController@login` validates credentials and creates token with `createToken('auth_token')`
- `AuthController@me` returns authenticated user with plan/specialty

Frontend:

- `axios.js` injects `Authorization` from `localStorage.auth_token`
- `AuthContext.jsx` calls `api.post('/login')`
- then calls `api.get('/me')`

### Current live production behavior

- frontend contract expects `/api/login` and `/api/me`
- live backend currently answers correctly on `/api/api/login` and `/api/api/me`
- therefore login fails before the intended auth lifecycle completes

## 5. Exception Handling Audit

### Active pipeline

Laravel 12 in this app uses:

- `bootstrap/app.php`
- not `app/Exceptions/Handler.php`

Current `app/Exceptions/Handler.php` is effectively a placeholder and not the real auth-exception control point.

### Active code path

File:

- `/Users/apple/Downloads/backup_crm_stable_zip_compressed/api/bootstrap/app.php`

Current hardening in workspace:

- custom `redirectGuestsTo(...)`
- custom `AuthenticationException` renderer
- custom `auth` alias to app middleware

These changes are aligned with API-only behavior, but live production evidence indicates they have not yet been deployed or route caches are masking them.

## 6. Frontend API Client Analysis

### Axios base URL

File:

- `/Users/apple/Downloads/backup_crm_stable_zip_compressed/frontend/src/api/axios.js`

Current behavior:

- `baseURL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'`
- sends `Accept: application/json`
- sends `Content-Type: application/json`
- sends bearer token if present

### Login flow

File:

- `/Users/apple/Downloads/backup_crm_stable_zip_compressed/frontend/src/context/AuthContext.jsx`

Behavior:

- startup hydration calls `GET /me` only if token exists
- login calls `POST /login`
- refresh calls `GET /me`

### User-visible error behavior

File:

- `/Users/apple/Downloads/backup_crm_stable_zip_compressed/frontend/src/utils/errorHandler.js`

Behavior:

- if `error.response` is missing, frontend shows:
  - `Network error. Please check your connection.`
- 401 shows:
  - `Session expired. Please login again.`
- 422 with validation errors shows first validation message

This explains the combined UX:

- backend login failure may show `Invalid credentials` from `Login.jsx`
- transport/CORS/baseURL issues may also show generic network error toast

## 7. CORS + Sanctum Analysis

### CORS

File:

- `/Users/apple/Downloads/backup_crm_stable_zip_compressed/api/config/cors.php`

Findings:

- allows `https://bioorganiccare.com`
- allows `https://www.bioorganiccare.com`
- allows localhost variants
- `supports_credentials = true`
- headers and methods are permissive

For bearer-token auth, this is acceptable.

### Sanctum

File:

- `/Users/apple/Downloads/backup_crm_stable_zip_compressed/api/config/sanctum.php`

Findings:

- `guard = ['web']`
- stateful domains list is mostly default localhost set
- no production domain explicitly configured for first-party cookie SPA auth

This is not the main live issue because the app is using bearer tokens, not cookie auth.

### Session / cookie config

File:

- `/Users/apple/Downloads/backup_crm_stable_zip_compressed/api/config/session.php`

Findings:

- `SESSION_DOMAIN=null`
- `same_site=lax`

Again, acceptable for bearer-token mode, but not a proper cross-subdomain cookie SPA setup if the project later wants true stateful Sanctum.

## 8. Deployment Pipeline Analysis

File:

- `/Users/apple/Downloads/backup_crm_stable_zip_compressed/deploy.sh`

### Good

- atomic release directories
- remote clone
- local frontend builds
- backend composer install
- package discovery
- migrations
- route/config/view cache
- health check and rollback

### Risks

1. Route prefix depends on `APP_ENV`, so route caching is highly sensitive.
2. Prior deployment flow did not clear caches before recaching.
3. Frontend is built locally, so build artifacts depend on the operator machine’s env files.
4. The deployment assumes mounting Laravel under `public/api`, which increases path complexity.

### Current hardening

Workspace `deploy.sh` now includes:

- `php artisan optimize:clear`

That is required before `config:cache` and `route:cache`.

## 9. API Contract Verification

### Local source contract

- `POST /api/login` exists
- `GET /api/me` exists
- `GET /api/health` exists externally in production because app is mounted under `/api`

### Local runtime verification

Observed locally:

- unauthenticated `GET /api/me` -> `401 {"message":"Unauthenticated."}`
- invalid `POST /api/login` -> `422` validation JSON

### Live production verification

Observed on 2026-03-14:

- `POST /api/login` -> broken
- `GET /api/me` -> broken
- `GET /api/health` -> works
- `POST /api/api/login` -> works
- `GET /api/api/me` -> works

### Contract verdict

Production contract is currently broken.

## 10. Frontend Network Failure Analysis

Likely causes of the observed `"Network error. Please check your connection."`:

1. local React env points to invalid base URL `https://bioorganiccare.com/api/public/api/`
2. websocket auth endpoint path may be wrong depending on environment
3. production API route mismatch returns unexpected non-contract responses
4. local dev may be talking to production instead of local backend

### Additional frontend defects

`SubscriptionContext.jsx` reads:

```js
const user = JSON.parse(localStorage.getItem('user') || '{}');
```

But `AuthContext.jsx` never writes `user` into localStorage.

Impact:

- `isDoctor` often resolves false on refresh
- subscription fetch can silently disable itself
- UI state can become inconsistent after login or reload

## 11. Production Path Analysis

### Confirmed production mount

Evidence from `deploy.sh` and live URLs:

- Laravel is mounted under `/public/api`
- frontend SPA is served from `/public/app`

### Confirmed live route mismatch

Because `https://bioorganiccare.com/api/api/login` currently works while `https://bioorganiccare.com/api/login` fails, the deployed route cache is not aligned with the intended production mount behavior.

Most likely reasons:

- stale route cache generated before env correction
- cached routes built while `APP_ENV` resolved non-production
- deployment not yet using current cache-clear hardening

## 12. Security Audit

### High

1. Bearer tokens are stored in `localStorage`.
   - XSS can exfiltrate tokens.
2. `api/.env` in workspace contains actual-looking secrets and DB credentials.
   - this is sensitive material and should not live in a distributable workspace or repo.
3. Reverb config allows `allowed_origins => ['*']`.
   - too permissive for production websockets.

### Medium

4. `Pusher.logToConsole = true` in frontend bootstrap.
   - leaks internal connection details to browser console.
5. No evidence that login is explicitly throttled.
   - `RateLimiter::for('api')` exists in `AppServiceProvider`, but no explicit `throttle:api` attachment was found in route middleware output.
6. `welcome.blade.php` still references `route('login')`.
   - stale server-rendered auth assumption.

## 13. Performance Audit

### Medium

1. `User::getEnabledModulesAttribute()` can trigger expensive resolution paths and cache work.
2. `AuthController@me` loads relations on every startup hydration request.
3. Slow query logging exists, but no structured observability or profiling pipeline is visible.

### Low

4. frontend login and auth refresh make sequential calls by design
5. large route tree with heavy middleware nesting will add overhead but is not the main incident cause

## 14. Exact Minimal Safe Fixes Required

### Immediate production fixes

1. Redeploy backend with cache clear before recaching.
2. On server, run:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
```

3. Verify live endpoints after deploy:
   - `POST https://bioorganiccare.com/api/login`
   - `GET https://bioorganiccare.com/api/me`
   - `GET https://bioorganiccare.com/api/health`

### Backend fixes

1. Keep Laravel in API mode for auth failures.
2. Ensure active code in `bootstrap/app.php` and `app/Http/Middleware/Authenticate.php` is deployed:
   - API requests return `401 JSON`
   - Laravel never falls back to `route('login')`
3. Keep explicit Sanctum `api` guard in `config/auth.php`.

### Frontend fixes

1. Fix local dev env:

```env
VITE_API_URL=http://localhost:8000/api
```

2. Remove malformed `https://bioorganiccare.com/api/public/api/` local default.
3. Stop relying on `localStorage.user` in `SubscriptionContext.jsx`, or write it consistently from auth state.

### Deployment / architecture fixes

1. Short term:
   - keep current `/public/api` mount
   - clear caches before recaching
   - verify route table after deploy
2. Long term:
   - remove env-dependent route prefixing, or
   - move API to a cleaner root-mounted app or dedicated API subdomain

The current mount-plus-conditional-prefix design works, but it is too easy to break with route caching.

## 15. Final Assessment

### What is broken right now

- production login contract is broken
- production auth profile contract is broken
- Laravel’s default guest redirect assumption is incompatible with SPA auth
- local frontend environment is misconfigured

### What must be true after repair

- React UI stays at `/app/login`
- backend auth stays at `/api/login`
- protected API requests return `401 JSON`
- production must not expose working auth routes under `/api/api/*`
- Laravel must never attempt `route('login')` for API auth failures

This system should be treated as API-only on the Laravel side, with route/cache handling made consistent with the `/public/api` production mount.
