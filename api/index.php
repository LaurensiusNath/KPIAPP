<?php

/*
|--------------------------------------------------------------------------
| Vercel Serverless Entry Point
|--------------------------------------------------------------------------
|
| /var/task is read-only on Vercel Lambda. Every Laravel writable path must
| be redirected to /tmp BEFORE the framework boots.
|
| The `env` block in vercel.json is only honored at build time on modern
| Vercel runtimes — at request time those values are NOT in $_ENV. So we
| set them here explicitly via putenv + $_ENV + $_SERVER so Laravel's
| Env::get() reads them no matter what.
|
*/

$writable = [
    'VIEW_COMPILED_PATH' => '/tmp/views',
    'APP_CONFIG_CACHE'   => '/tmp/config.php',
    'APP_EVENTS_CACHE'   => '/tmp/events.php',
    'APP_PACKAGES_CACHE' => '/tmp/packages.php',
    'APP_ROUTES_CACHE'   => '/tmp/routes.php',
    'APP_SERVICES_CACHE' => '/tmp/services.php',
    'CACHE_DRIVER'       => 'array',
    'CACHE_STORE'        => 'array',
    'LOG_CHANNEL'        => 'stderr',
    'SESSION_DRIVER'     => 'cookie',
];

foreach ($writable as $key => $value) {
    // Only set if not already provided by the runtime, so Vercel dashboard env wins.
    if (getenv($key) === false || getenv($key) === '') {
        putenv("{$key}={$value}");
        $_ENV[$key]    = $value;
        $_SERVER[$key] = $value;
    }
}

// Make sure all writable subdirectories under /tmp exist.
foreach (['/tmp/storage', '/tmp/storage/framework', '/tmp/storage/framework/views',
          '/tmp/storage/framework/cache', '/tmp/storage/framework/sessions',
          '/tmp/storage/logs', '/tmp/views', '/tmp/cache'] as $dir) {
    if (! is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

require __DIR__ . '/../public/index.php';
