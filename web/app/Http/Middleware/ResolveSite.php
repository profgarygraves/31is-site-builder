<?php

namespace App\Http\Middleware;

use App\Models\Site;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Extracts the subdomain from the request HOST and attaches the matching
 * Site model to the request as an attribute. 404s if no site found or
 * if the site is unpublished.
 *
 * The "app" subdomain (or apex) is reserved for the dashboard and is
 * NOT routed through this middleware — see routes/web.php.
 */
class ResolveSite
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $parent = config('app.parent_domain');
        $appSub = config('app.app_subdomain');

        // Strip the parent domain from the host to get the subdomain label.
        // host: "gravesbros.31is.test" -> "gravesbros"
        // host: "31is.test"            -> "" (apex)
        // host: "app.31is.test"        -> "app"
        $sub = '';
        if ($host === $parent) {
            $sub = '';
        } elseif (str_ends_with($host, '.' . $parent)) {
            $sub = substr($host, 0, -1 - strlen($parent));
        }

        // Apex and dashboard subdomain are NOT student sites.
        if ($sub === '' || $sub === $appSub) {
            abort(404);
        }

        $site = Site::where('subdomain', strtolower($sub))
            ->where('is_published', true)
            ->first();

        if (! $site) {
            abort(404);
        }

        $request->attributes->set('site', $site);

        return $next($request);
    }
}
