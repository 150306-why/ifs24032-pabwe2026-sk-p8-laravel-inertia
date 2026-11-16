<?php

use App\Features\Shared\Middleware\EnsureRole;
use App\Features\Shared\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Aplikasi berjalan di belakang reverse proxy (Delcom): percayai header X-Forwarded-*.
        $middleware->trustProxies(at: '*');
        $middleware->web(append: [HandleInertiaRequests::class]);
        $middleware->alias(['role' => EnsureRole::class]);
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn (Request $request) => $request->user()->homeUrl());
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
