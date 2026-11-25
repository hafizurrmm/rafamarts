<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // ওয়েব ইনস্টলার: সেশন/CSRF ছাড়া, কারণ তখনো .env ও ডাটাবেস নেই
            Route::middleware([])->group(base_path('routes/install.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // নতুন আনপ্যাক করা সাইটে (APP_KEY নেই) যেকোনো পেজ খুললে ইনস্টলারে পাঠায়
        $exceptions->render(function (\Illuminate\Encryption\MissingAppKeyException $e, Request $request) {
            if (! is_file(storage_path('app/installed.lock')) && ! $request->is('install')) {
                return redirect('/install');
            }
        });
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
