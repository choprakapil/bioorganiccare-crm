<?php
 
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
 
define('LARAVEL_START', microtime(true));
 
/*
|--------------------------------------------------------------------------
| Environment-Aware Path Detection (Atomic Deployment Friendly)
|--------------------------------------------------------------------------
|
| 1. Local Dev: Paths are typically ../vendor
| 2. Atomic Release: Paths are typically ../../api/vendor (engine is sibling to public)
| 3. Hostinger Legacy: Paths might be ../../project/api/vendor
|
*/
 
$basePath = null;
 
if (file_exists(__DIR__.'/../vendor/autoload.php')) {
    // Local / Standard
    $basePath = __DIR__.'/..';
} elseif (file_exists(__DIR__.'/../../api/vendor/autoload.php')) {
    // Atomic Release Structure (current/public/api -> current/api)
    $basePath = __DIR__.'/../../api';
} elseif (file_exists(__DIR__.'/../../project/api/vendor/autoload.php')) {
    // Hostinger Split Structure
    $basePath = __DIR__.'/../../project/api';
}
 
// Fallback to standard if nothing found to prevent crash before handled by autoloader
if (!$basePath) $basePath = __DIR__.'/..';
 
// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $basePath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}
 
// Register the Composer autoloader...
require $basePath.'/vendor/autoload.php';
 
// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $basePath.'/bootstrap/app.php';
 
$app->handleRequest(Request::capture());
