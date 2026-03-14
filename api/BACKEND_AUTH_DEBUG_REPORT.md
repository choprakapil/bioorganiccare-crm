# BACKEND AUTH DEBUG REPORT

## 1. Root Cause of the Error

Primary root cause:
Laravel 12 was using the framework default guest redirect fallback for unauthenticated requests. When an `AuthenticationException` was rendered for a request that did not resolve as JSON, Laravel fell back to `route('login')`. This project has no named `login` route, so the framework threw:

`The route login could not be found.`

Secondary production-critical root cause:
the application relies on environment-dependent route prefixing in `bootstrap/app.php`:

```php
->prefix(trim(env('APP_ENV')) === 'production' ? '' : 'api')
```

This is only safe because production is deployed under the `/api` subdirectory. Externally the SPA still calls `/api/login`, but internally Laravel resolves `/login` inside that mounted app. That coupling is fragile, and stale route/config caches can break the mapping.

Direct production evidence collected on 2026-03-14:

- `https://bioorganiccare.com/app/login` returned the React HTML shell successfully.
- `POST https://bioorganiccare.com/api/login` returned JSON error: `The route login could not be found.`
- `GET https://bioorganiccare.com/api/me` returned JSON/HTML route resolution errors instead of the expected `401 Unauthenticated`.

## 2. File Where the Error Originates

Framework origin of the redirect fallback:

- `vendor/laravel/framework/src/Illuminate/Foundation/Exceptions/Handler.php`
- Method: `unauthenticated()`
- Relevant logic:
  - non-JSON requests are redirected via:
  - `redirect()->guest($exception->redirectTo($request) ?? route('login'))`

Framework default that installs the guest redirect:

- `vendor/laravel/framework/src/Illuminate/Foundation/Configuration/ApplicationBuilder.php`
- `withMiddleware()` sets:
  - `redirectGuestsTo(fn () => route('login'))`

Project file that caused production route mismatch:

- `bootstrap/app.php`
- API routes were conditionally unprefixed in production.

## 3. Middleware Chain Involved

For protected API routes such as `GET /api/me`:

1. `api`
2. `Illuminate\Auth\Middleware\Authenticate:sanctum`
3. `App\Http\Middleware\ResolveTenantContext`
4. `App\Http\Middleware\RequireSpecialtyForAdmin`
5. Additional route-specific middleware on nested groups

Failure path before fix:

1. React or another client hits protected endpoint without valid auth.
2. `auth:sanctum` throws `AuthenticationException`.
3. Laravel exception handler decides request is not JSON-safe enough for JSON response in some cases.
4. Handler falls back to redirect guest to `route('login')`.
5. No named `login` route exists.
6. Laravel throws `RouteNotFoundException`.

## 4. Route Resolution Analysis

Local route audit after fix:

- `POST /api/login` exists.
- `GET /api/me` exists.
- No named `login` route exists.
- No `GET /login` route exists.
- No named `api/login` route exists.

Observed production behavior:

- Frontend expects external URLs like `/api/login`.
- Production Laravel is mounted under the `/api` subdirectory, so internal Laravel route definitions do not necessarily include the `api` path segment.
- Local `php artisan route:list` therefore cannot be compared 1:1 to production URLs without accounting for that mount point.

This is incompatible with:

- `frontend/src/api/axios.js`
- `frontend/src/context/AuthContext.jsx`
- deployment health checks in `deploy.sh`
- multiple feature tests under `api/tests/Feature/*`

It also means route/cache state has to be rebuilt consistently on deploy.

## 5. Sanctum Authentication Flow

Current architecture is token-based SPA auth, not Blade/web auth:

1. React login page submits `POST /api/login`.
2. `AuthController@login` validates credentials and issues a Sanctum token.
3. React stores token in `localStorage`.
4. Axios sends `Authorization: Bearer <token>` for protected requests.
5. Protected API routes use `auth:sanctum`.
6. Unauthenticated requests must return `401 JSON`, not redirect to a web login route.

Relevant findings:

- `routes/api.php` correctly defines `POST /login`.
- Protected routes correctly use `auth:sanctum`.
- Frontend correctly calls `api.post('/login')`, which maps to `POST /api/login`.
- Frontend also calls `api.get('/me')` on startup when a token exists.
- No React code path was found calling `GET /login`.

## 6. Exact Fix Applied

### A. Added project-level auth middleware override

Created:

- `app/Http/Middleware/Authenticate.php`

Behavior:

- Returns `null` redirect target for JSON requests and requests routed through the `api` middleware group.
- Redirects browser-style non-API guest access to `/app/login`.

This avoids depending on URL path detection, which is brittle because production is mounted under `/api`.

### B. Bound the `auth` middleware alias to the custom middleware

Updated `bootstrap/app.php` middleware aliases:

- `'auth' => \App\Http\Middleware\Authenticate::class`

This ensures `auth:sanctum` uses the project override rather than the framework default.

### C. Overrode guest redirect handling at the application bootstrap layer

Updated `bootstrap/app.php`:

- Added `$middleware->redirectGuestsTo(...)`
- JSON requests and `api` middleware-group requests return `null`
- non-API guest requests redirect to `/app/login`

This removes the framework default `route('login')` assumption.

### D. Hardened authentication exception rendering

Updated `bootstrap/app.php` exception handling:

- API and JSON requests now return:

```json
{"message":"Unauthenticated."}
```

with HTTP `401`.

- Non-API browser requests redirect to `/app/login`.

### E. Added Sanctum API guard

Updated `config/auth.php`:

- Added:

```php
'api' => [
    'driver' => 'sanctum',
    'provider' => 'users',
],
```

This aligns guard configuration with the API-first architecture.

### F. Fixed deployment cache clearing

Updated `deploy.sh`:

- Added `php artisan optimize:clear` before `config:cache` and `route:cache`

Reason:
Prevents stale route/config caches from preserving broken auth or route definitions across deploys.

## 7. Verification Performed

Local verification after fix:

- `php artisan route:list` shows:
  - `POST api/login`
  - `GET|HEAD api/me`
- `curl http://127.0.0.1:8090/api/me` returns:
  - `401`
  - `{"message":"Unauthenticated."}`
- `curl -H 'Accept: application/json' http://127.0.0.1:8090/api/me` returns:
  - `401`
  - `{"message":"Unauthenticated."}`
- `curl -X POST http://127.0.0.1:8090/api/login` with bad credentials returns:
  - `422`
  - validation JSON, not redirect

Limitations:

- `php artisan test` is unavailable in this workspace because dev binaries are not installed.
- `vendor/bin/phpunit` is also absent for the same reason.

## 8. Cause Classification

Requested classification result:

- A) Default Laravel Authenticate middleware redirect: Yes
- B) Exception handler redirect: Yes
- C) Sanctum middleware redirect: Indirectly through `auth:sanctum`, not Sanctum-specific custom code
- D) React hitting protected endpoint before login: No primary evidence; startup `/me` call only happens when token exists
- E) Missing JSON detection in authentication exception: Yes, this was part of the failure path
- F) Middleware stack misconfiguration: Yes, because the app was using framework defaults incompatible with API-only auth

## 9. Additional Architectural Issues Found

1. Production API prefixing was environment-dependent and broke the SPA contract.
2. `config/auth.php` had no explicit `api` guard.
3. `app/Exceptions/Handler.php` is a placeholder and not part of Laravel 12’s active exception pipeline, so debugging there would be misleading.
4. `resources/views/welcome.blade.php` still references `route('login')`; it is not part of the SPA auth flow, but it is stale Blade-era logic.
5. Production routing depends on the app being mounted under `/api`; that makes route caching and environment correctness more sensitive than a standard root-mounted Laravel API.
6. Deployment previously skipped `optimize:clear`, which increases the chance of stale route/auth behavior after release.

## 10. Final Assessment

The correct backend behavior for this architecture is:

- React SPA handles login UI at `/app/login`
- Laravel exposes auth endpoints only under `/api/*`
- protected API requests use `auth:sanctum`
- unauthenticated API access returns `401 JSON`
- Laravel must never attempt to resolve `route('login')` for API auth failures

That behavior is now implemented in code.
