<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sets a restrictive Content-Security-Policy on student-site responses.
 *
 * The CSP is permissive enough that AI-generated HTML with inline styles
 * and inline <script> tags still works (we strip <script> in the
 * sanitizer; CSP allows our injected shim and any styling), but
 * restrictive enough to make casual XSS hard.
 *
 * We also force `noindex` until we have trust scoring — one bad student
 * site flagged by Google could taint the parent domain.
 */
class InjectCSP
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $csp = implode('; ', [
            "default-src 'self' https: data:",
            "img-src 'self' https: data: blob:",
            "style-src 'self' 'unsafe-inline' https:",
            "script-src 'self' 'unsafe-inline'",
            "font-src 'self' https: data:",
            "connect-src 'self' https:",
            "frame-src https://www.youtube.com https://www.youtube-nocookie.com https://player.vimeo.com",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);

        $response->headers->set('Content-Security-Policy', $csp);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $response;
    }
}
