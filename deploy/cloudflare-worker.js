/**
 * Cloudflare Worker: Host-rewrite for *.31is.com → origin
 *
 * Why: GoDaddy's shared cPanel hosting won't add ServerAlias *.31is.com to
 * the Apache vhost on our behalf, so the origin Apache can only serve named
 * subdomains we've manually added in cPanel. This Worker sits at the edge
 * and rewrites the Host header on every *.31is.com request to "31is.com",
 * which Apache routes to our Laravel doc root via the existing vhost.
 *
 * Laravel still needs to know the original subdomain for routing — we
 * pass it via X-Forwarded-Host. With Laravel's TrustProxies configured
 * to trust Cloudflare IPs and respect that header, $request->getHost()
 * returns the original "smartwash.31is.com" — so our existing Route::domain
 * patterns work unchanged.
 *
 * Pass-through hosts (don't rewrite): the apex and app.31is.com both have
 * proper vhost entries in cPanel, so let those flow through normally.
 *
 * Worker binding (configure in Cloudflare dashboard → Workers & Pages →
 * this worker → Triggers → Add Custom Domain or Route):
 *   Route: *.31is.com/*
 *   Zone:  31is.com
 *
 * (Don't bind to app.31is.com or 31is.com — those should bypass the Worker
 * entirely. The route pattern *.31is.com only matches subdomains by default,
 * not the apex.)
 */

export default {
  async fetch(request) {
    const url = new URL(request.url);
    const originalHost = url.hostname;

    // Belt-and-suspenders: even if the route pattern matches the dashboard
    // host, don't rewrite it. The dashboard is a proper subdomain on origin.
    if (originalHost === 'app.31is.com' || originalHost === '31is.com') {
      return fetch(request);
    }

    // Rewrite the destination URL hostname → 31is.com. fetch() will use the
    // URL's hostname for both DNS lookup AND the Host header it sends.
    url.hostname = '31is.com';

    // Carry the original subdomain forward for Laravel via X-Forwarded-Host.
    // (X-Forwarded-For/Proto are already added by Cloudflare automatically.)
    const headers = new Headers(request.headers);
    headers.set('X-Forwarded-Host', originalHost);

    return fetch(url, {
      method: request.method,
      headers,
      body: request.body,
      redirect: 'manual',
    });
  },
};
