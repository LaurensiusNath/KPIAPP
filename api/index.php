<?php

/*
|--------------------------------------------------------------------------
| Vercel Serverless Entry Point
|--------------------------------------------------------------------------
|
| /var/task is read-only on Vercel Lambda. We MUST redirect every Laravel
| writable path to /tmp BEFORE the framework boots. We do this by setting
| environment variables here (putenv + $_ENV + $_SERVER) so that they are
| picked up consistently regardless of Vercel's env propagation timing or
| any cached config.
|
*/

$writable = [
    'VIEW_COMPILED_PATH'   => '/tmp/views',
    'APP_CONFIG_CACHE'     => '/tmp/config.php',
    'APP_EVENTS_CACHE'     => '/tmp/events.php',
    'APP_PACKAGES_CACHE'   => '/tmp/packages.php',
    'APP_ROUTES_CACHE'     => '/tmp/routes.php',
    'APP_SERVICES_CACHE'   => '/tmp/services.php',
    'CACHE_DRIVER'         => 'array',
    'CACHE_STORE'          => 'array',
    'LOG_CHANNEL'          => 'stderr',
    'SESSION_DRIVER'       => 'cookie',
];

foreach ($writable as $key => $value) {
    putenv("{$key}={$value}");
    $_ENV[$key]    = $value;
    $_SERVER[$key] = $value;
}

// Make sure subdirectories Laravel may try to write into actually exist.
foreach (['/tmp/views', '/tmp/cache', '/tmp/sessions', '/tmp/logs'] as $dir) {
    if (! is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

require __DIR__ . '/../public/index.php';
