<?php

use Illuminate\Http\Request;

// PHP 8.5 deprecates Pdo\Mysql::ATTR_SSL_CA constants that Laravel 11's
// config/database.php still uses. Suppress the noisy warnings until the
// framework is updated; they're harmless.
error_reporting(error_reporting() & ~E_DEPRECATED & ~E_USER_DEPRECATED);

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
