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

        // Trust Cloudflare as a proxy. Cloudflare rewrites Host → 31is.com
        // when forwarding to origin (so our Apache vhost matches), then passes
        // the visitor's original hostname via X-Forwarded-Host. Trusting this
        // header makes $request->getHost() return e.g. "smartwash.31is.com"
        // again so our Route::domain('{subdomain}.31is.com') patterns still
        // match. Trust ALL proxies because (after the Cloudflare migration)
        // every request to origin is via Cloudflare; the only realistic
        // alternative path is direct-to-IP curl which is fine to ignore.
        $middleware->trustProxies(
            at: '*',
            headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR
                | \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST
                | \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT
                | \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO,
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
