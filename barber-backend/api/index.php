<?php
// Override cache paths to /tmp since Vercel is read-only
putenv('APP_CONFIG_CACHE=/tmp/config.php');
putenv('APP_EVENTS_CACHE=/tmp/events.php');
putenv('APP_PACKAGES_CACHE=/tmp/packages.php');
putenv('APP_ROUTES_CACHE=/tmp/routes.php');
putenv('APP_SERVICES_CACHE=/tmp/services.php');
putenv('VIEW_COMPILED_PATH=/tmp');
$_ENV['APP_CONFIG_CACHE'] = '/tmp/config.php';
$_ENV['APP_EVENTS_CACHE'] = '/tmp/events.php';
$_ENV['APP_PACKAGES_CACHE'] = '/tmp/packages.php';
$_ENV['APP_ROUTES_CACHE'] = '/tmp/routes.php';
$_ENV['APP_SERVICES_CACHE'] = '/tmp/services.php';
$_ENV['VIEW_COMPILED_PATH'] = '/tmp';

$storagePath = '/tmp/storage';
if (!is_dir($storagePath)) {
    mkdir($storagePath, 0777, true);
    mkdir($storagePath.'/framework/views', 0777, true);
    mkdir($storagePath.'/framework/cache', 0777, true);
    mkdir($storagePath.'/framework/cache/data', 0777, true);
    mkdir($storagePath.'/framework/sessions', 0777, true);
    mkdir($storagePath.'/logs', 0777, true);
}

// Forward Vercel requests to normal Laravel public/index.php
require __DIR__ . '/../public/index.php';
