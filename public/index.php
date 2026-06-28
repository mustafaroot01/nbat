<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// When using the PHP built-in server (php artisan serve), requests to an
// existing directory like /dashboard cause PHP to set SCRIPT_NAME to the
// directory's index.html, which makes Laravel strip the /dashboard prefix and
// mis-route SPA sub-paths (e.g. /dashboard/login). Normalize the server vars so
// the full request path reaches the router. This only affects local dev; on
// Apache/Nginx (production) the SAPI is not 'cli-server' and this is skipped.
if (PHP_SAPI === 'cli-server') {
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    $_SERVER['SCRIPT_FILENAME'] = __DIR__.'/index.php';
    $_SERVER['PHP_SELF'] = '/index.php';
    unset($_SERVER['PATH_INFO']);
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
