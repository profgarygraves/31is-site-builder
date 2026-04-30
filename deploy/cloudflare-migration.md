# Cloudflare migration — wildcard subdomain + free HTTPS

Solves two problems at once:
1. GoDaddy won't add `ServerAlias *.31is.com` to the Apache vhost (shared cPanel limitation), so right now we manually add named subdomains in cPanel for every student site.
2. We're running HTTP-only — browsers show "Not Secure".

After this migration, *any* `<word>.31is.com` automatically routes to the Laravel app, and everything is over HTTPS (free wildcard cert via Cloudflare).

## Architecture

```
Browser → https://smartwash.31is.com → Cloudflare edge
                                         │
                                         ├─ Worker rewrites Host → 31is.com
                                         │  Adds X-Forwarded-Host: smartwash.31is.com
                                         │
                                         ▼
                                       Origin (208.109.22.54)
                                         │
                                         ├─ Apache routes Host=31is.com to existing vhost
                                         │  Doc root: /public_html/31is.com/web/public/
                                         │
                                         ▼
                                       Laravel
                                         │
                                         ├─ TrustProxies reads X-Forwarded-Host
                                         ├─ $request->getHost() returns "smartwash.31is.com"
                                         └─ Route::domain('{subdomain}.31is.com') matches as before
```

## Steps

### 1. Sign up at Cloudflare (5 min)

1. https://cloudflare.com → Sign up (free)
2. **+ Add a Site** → enter `31is.com`
3. Pick the **Free** plan at the bottom
4. Cloudflare scans existing GoDaddy/NSI DNS and imports records
5. **Verify the import** — make sure these are present:
   - `A` record: `@` (apex) → `208.109.22.54`
   - `A` record: `*` → `208.109.22.54` (the wildcard)
   - `A` record: `app` → `208.109.22.54`
   - `MX` records (for email)
   - any `TXT` (SPF, DKIM for Resend, etc.)
6. **All `A` records should be set to "Proxied" (orange cloud)** — this is what enables HTTPS and CDN
7. Cloudflare assigns 2 nameservers (e.g. `dexter.ns.cloudflare.com`, `tegan.ns.cloudflare.com`). Copy them.

### 2. Change nameservers at the registrar (5 min + propagation)

The domain is registered at **Network Solutions** (we discovered earlier), not GoDaddy.

1. Log into https://www.networksolutions.com
2. Manage `31is.com` → Nameservers / DNS settings
3. Change from `ns59.domaincontrol.com` / `ns60.domaincontrol.com` to the two Cloudflare nameservers
4. Save

Propagation usually takes 5–30 min for Network Solutions → Cloudflare. Existing requests keep working through GoDaddy DNS during the transition. Cloudflare's dashboard will show "Active" once they've taken over.

### 3. Set SSL/TLS to Flexible (1 min)

In Cloudflare → **SSL/TLS → Overview** → set encryption mode to **Flexible**.

- "Flexible" = visitor↔Cloudflare is HTTPS, Cloudflare↔origin is plain HTTP
- This is fine for now (origin already accepts HTTP)
- We can upgrade to "Full (strict)" later by installing a Cloudflare Origin Certificate in cPanel

Then **SSL/TLS → Edge Certificates** → toggle **Always Use HTTPS** = ON. This redirects visitors from `http://` to `https://` automatically.

### 4. Create the Worker (5 min)

1. Cloudflare dashboard → **Workers & Pages → Create application → Create Worker**
2. Name: `host-rewrite-31is`
3. Replace the default code with the contents of `deploy/cloudflare-worker.js` from this repo
4. **Save and Deploy**
5. Click into the deployed worker → **Triggers → Add Custom Domain or Route**
6. Add a route:
   - Route: `*.31is.com/*`
   - Zone: `31is.com`
7. Save

The Worker now intercepts every `<word>.31is.com` request. It does NOT intercept `31is.com` (apex) or `app.31is.com` — those go straight to origin.

### 5. Deploy Laravel changes (1 min)

The TrustProxies update is already on `main`. Pull it in:

```bash
ssh -i ~/.ssh/web31_rsa lcfl5uhr556v@p3plzcpnl445407.prod.phx3.secureserver.net
~/sync.sh
```

Also update `.env` to use HTTPS for app URLs:

```bash
cd ~/public_html/31is.com/web
nano .env
```

Change two lines:

```env
APP_URL=https://app.31is.com
APP_URL_SCHEME=https
```

Save, then:

```bash
php artisan config:cache
```

### 6. Verify

Once Cloudflare shows "Active":

```bash
# Should resolve to Cloudflare IP, not 208.109.22.54
dig +short app.31is.com

# Should return 200 with Cloudflare headers
curl -sI https://app.31is.com/login | head -10

# Wildcard subdomain — try a name you've NEVER added in cPanel
curl -sI https://wildcardtest$(date +%s).31is.com/ | head -10
# Expected: HTTP/2 404 — Laravel returned 404 for unknown subdomain.
# That's the win: Apache routed it to our vhost via the Worker's
# Host rewrite, Laravel ran, ResolveSite middleware 404'd because
# no published site matches that subdomain.
```

If the wildcard test returns 404 (not the cPanel "Future home" placeholder), wildcard routing works — every new student site becomes immediately reachable when they hit Save in the dashboard.

### 7. Test end-to-end

In a fresh incognito window:
1. https://app.31is.com/register → register
2. Create a site at any subdomain
3. Visit that subdomain — should render with HTTPS lock icon
4. Submit a lead form
5. Verify lead in dashboard

## Rollback

If something goes wrong:
- **Network Solutions** → change nameservers back to `ns59.domaincontrol.com` / `ns60.domaincontrol.com`. Wait propagation.
- The Cloudflare account stays; you can come back to it.

## Future hardening (post-event)

- **SSL Mode "Full (strict)"** — generate a 15-year Cloudflare Origin Certificate, install in cPanel WHM, set Cloudflare SSL mode to "Full (strict)". Then both legs are HTTPS and origin certs are strictly verified.
- **Page Rules** — set caching policy for `/storage/sites/*` (image uploads) so they're CDN-cached. Cuts load on the cPanel server during traffic spikes.
- **Rate limiting** — Cloudflare's free tier has basic rate limiting; useful for `/__lead/*` to prevent form spam.
- **DDoS / Bot Fight Mode** — already on by default at "Free" tier.
