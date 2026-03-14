# Deploy Failure Diagnosis Report

## Root Cause

The failed deployment was caused by generated Laravel cache artifacts being committed in `api/bootstrap/cache/` and cloned onto the server.

Those files contained absolute local macOS paths such as:

```text
/Users/apple/Downloads/backup_crm_stable_zip_compressed/api/storage/logs/laravel.log
/Users/apple/Downloads/backup_crm_stable_zip_compressed/api/storage/framework/views
```

When the new release booted on Hostinger, Laravel attempted to use those invalid local paths during package discovery and the health check, which caused the release to fail before the remote cache could be rebuilt.

## Evidence

Observed deploy failure:

```text
There is no existing directory at "/Users/apple/Downloads/backup_crm_stable_zip_compressed/api/storage/logs" and it could not be created: Permission denied
```

Observed health check failure:

```text
file_put_contents(/Users/apple/Downloads/backup_crm_stable_zip_compressed/api/storage/framework/views/bdc2812a1f8fca8592c9e3d031018bcf.php): Failed to open stream: No such file or directory
```

Repository evidence before fix:

```text
api/bootstrap/cache/config.php
api/bootstrap/cache/packages.php
api/bootstrap/cache/routes-v7.php
api/bootstrap/cache/services.php
```

Absolute-path matches found in tracked cache files:

```text
api/bootstrap/cache/config.php:649: 'path' => '/Users/apple/Downloads/backup_crm_stable_zip_compressed/api/storage/logs/laravel.log',
api/bootstrap/cache/config.php:30: 'compiled' => '/Users/apple/Downloads/backup_crm_stable_zip_compressed/api/storage/framework/views',
api/bootstrap/cache/routes-v7.php:10697: View::file('/Users/apple/Downloads/backup_crm_stable_zip_compressed/api/vendor/laravel/framework/src/Illuminate/Foundation/Configuration/../resources/health-up.blade.php'
```

Secondary deploy-script issue:

```text
mysqldump: Got error: 1045: "Access denied for user ..."
✅ Backup saved.
```

The deploy script was reporting backup success even when the dump failed.

## Fix Applied

### Source control hygiene

- deleted generated cache files from `api/bootstrap/cache/`
- added `api/bootstrap/cache/.gitignore`
- updated root `.gitignore` to ignore `api/` runtime artifacts instead of only `backend/`

### Deploy hardening

Updated `deploy.sh` to:

- use `set -euo pipefail`
- fail the deploy if database backup fails
- remove any cloned `bootstrap/cache/*.php` files before Laravel boots on the server
- ensure required storage/framework directories exist before running Artisan

## Files Changed

- `.gitignore`
- `deploy.sh`
- `api/bootstrap/cache/.gitignore`
- deleted:
  - `api/bootstrap/cache/config.php`
  - `api/bootstrap/cache/packages.php`
  - `api/bootstrap/cache/routes-v7.php`
  - `api/bootstrap/cache/services.php`

## Local Verification

### Cache cleanup

```text
$ php artisan optimize:clear
INFO  Clearing cached bootstrap files.
config DONE
cache DONE
compiled DONE
events DONE
routes DONE
views DONE
```

### Route contract

```text
$ php artisan route:list | grep -E '(^\s*POST\s+login|^\s*GET\|HEAD\s+me\b|health)'
GET|HEAD        health
POST            login ................................. AuthController@login
GET|HEAD        me ....................................... AuthController@me
```

### HTTP contract

```text
$ curl -i -X POST http://127.0.0.1:8000/login -H 'Accept: application/json' -H 'Content-Type: application/json' -d '{}'
HTTP/1.1 422 Unprocessable Content
{"message":"The email field is required. (and 1 more error)","errors":{"email":["The email field is required."],"password":["The password field is required."]}}
```

```text
$ curl -i http://127.0.0.1:8000/me -H 'Accept: application/json'
HTTP/1.1 401 Unauthorized
{"message":"Unauthenticated."}
```

```text
$ curl -i http://127.0.0.1:8000/health -H 'Accept: application/json'
HTTP/1.1 200 OK
{"status":"ok","time":"2026-03-14T10:43:12.339221Z"}
```

## Deployment Note

The deleted cache files are still shown by `git ls-files` until the deletion commit is created and pushed. The next deploy must use the commit that removes those files, otherwise the server will clone them again.

## Decision

The deploy failure root cause is fixed in source. The next safe step is:

1. commit the cache-file deletions and deploy-script changes
2. push to the deployment branch
3. rerun `./deploy.sh`
