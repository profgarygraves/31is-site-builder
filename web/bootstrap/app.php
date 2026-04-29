<?php

use App\Http\Middleware\InjectCSP;
use App\Http\Middleware\ResolveSite;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Aliases used in route definitions in routes/web.php.
        $middleware->alias([
            'resolve.site' => ResolveSite::class,
            'inject.csp' => InjectCSP::class,
        ]);

        // Form-action rewriting on student sites means visitor submissions
        // do NOT carry a Laravel session/CSRF token. Exclude the lead
        // endpoint from CSRF — we use a per-site HMAC token instead
        // (validated in LeadController).
        $middleware->validateCsrfTokens(except: [
            '__lead/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
