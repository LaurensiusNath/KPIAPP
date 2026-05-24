<?php

use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SetTestTime;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['role' => RoleMiddleware::class]);
        // Apply Carbon::setTestNow() per request for the testing time selector
        $middleware->web(append: [SetTestTime::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

/*
|--------------------------------------------------------------------------
| Vercel Lambda: redirect storage to /tmp
|--------------------------------------------------------------------------
|
| /var/task is read-only in Vercel. Point storage_path() at a writable
| location so Blade compiled views, logs, and the like all work.
|
*/
if (! empty($_SERVER['VERCEL']) || ! empty(getenv('VERCEL'))) {
    if (! is_dir('/tmp/storage/framework/views')) {
        @mkdir('/tmp/storage/framework/views', 0777, true);
    }
    if (! is_dir('/tmp/storage/framework/cache/data')) {
        @mkdir('/tmp/storage/framework/cache/data', 0777, true);
    }
    if (! is_dir('/tmp/storage/framework/sessions')) {
        @mkdir('/tmp/storage/framework/sessions', 0777, true);
    }
    if (! is_dir('/tmp/storage/logs')) {
        @mkdir('/tmp/storage/logs', 0777, true);
    }

    $app->useStoragePath('/tmp/storage');
}

return $app;
