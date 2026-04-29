# Production deploy to GoDaddy cPanel — 31is.com

Two phases. **Phase 1 you do in the cPanel UI** (only you can — it's your account). **Phase 2 I do over SSH** once you've shared credentials.

Mode: HTTP-only for the upcoming Startup Weekend test. Wildcard SSL is deferred.

---

## Phase 1 — your hands-on cPanel work (~20 min)

### 1.1 Back up the existing WordPress site

In cPanel:
- **Files → File Manager** → right-click `public_html`, **Compress** → download the zip
- **Databases → phpMyAdmin** → select the WP database → **Export → Quick → SQL** → download

Stash both somewhere safe. After this we'll wipe `public_html` to make room for Laravel.

### 1.2 Enable SSH access

Most GoDaddy cPanel plans support SSH but it's off by default.

- **Security → SSH Access** (or "Manage SSH Keys")
- If GoDaddy requires it, generate or import a key, or enable password auth
- Note the **SSH host**, **port** (often 22 or a non-standard one like 2222), and **username**

If you can't find SSH Access in cPanel, search GoDaddy support for "Enable SSH access cPanel" — it's plan-dependent. If your plan doesn't include SSH, fall back to cPanel's **Advanced → Terminal** feature (browser-based shell) and we'll run the same commands there.

### 1.3 Create a MySQL database

- **Databases → MySQL Databases**
- Create database: `31is_app` (cPanel will prefix it, e.g. `youruser_31is_app`)
- Create user: `31is_app` (will become `youruser_31is_app`)
- Add user to database with **ALL PRIVILEGES**
- Save the full names + password somewhere safe — we need them for `.env`

### 1.4 Create an email account for outbound mail

- **Email → Email Accounts** → **Create**
- Address: `noreply@31is.com` (or `hello@31is.com` — pick one)
- Set a strong password
- Note the SMTP settings — usually:
  - Host: `<your-server>.secureserver.net` or similar (cPanel shows it)
  - Port: 465 (SSL) or 587 (TLS)
  - Username: full email address
  - Password: what you just set

### 1.5 Find your server IP

- **General Information** sidebar in cPanel → look for "Shared IP Address" or "Server IP"
- Or in **Domains → Zone Editor** for 31is.com, look at the existing A record
- You'll need this to verify wildcard DNS works

### 1.6 Set up wildcard DNS

In **Domains → Zone Editor** for 31is.com:
- Add an `A` record: name = `*`, value = your server IP, TTL = 14400
- Confirm there's also an `A` record for `app` pointing to the same IP (or a wildcard already covers it — both work)

After saving, run from your laptop: `dig random-test.31is.com` — should resolve to your server IP within a minute or two.

### 1.7 Configure subdomains in cPanel

- **Domains → Domains** (or "Subdomains" on older cPanel)
- Add `app.31is.com` — for now leave the document root as default; we'll point it at `web/public` after deploy.
- Add `*.31is.com` (literal asterisk) as a wildcard subdomain. Same — point document root later.

(If cPanel won't let you save `*` as a subdomain, skip this — we can use `.htaccess` rewrites instead. Tell me which.)

### 1.8 Hand me the credentials

When done, send me (in a single message — I'll never log them in transcripts):

```
SSH host:        ?
SSH port:        ?
SSH username:    ?
SSH password:    ?   (or attach a private key)

cPanel username: ?    (often same as SSH username)
Server IP:       ?

MySQL DB name:   <prefix>_31is_app
MySQL user:      <prefix>_31is_app
MySQL password:  ?

SMTP host:       ?
SMTP port:       ?
SMTP user:       noreply@31is.com
SMTP password:   ?
SMTP from:       noreply@31is.com (or hello@31is.com)
```

---

## Phase 2 — what I do over SSH (~15 min, once I have creds)

1. SSH in, `cd ~/`
2. `git clone https://github.com/profgarygraves/31is-site-builder.git`
3. `cd 31is-site-builder/deploy && bash bootstrap.sh`
   The script:
   - Runs `composer install --no-dev --optimize-autoloader`
   - Copies `env.production.example` to `web/.env`, prompts for the credentials
   - Generates `APP_KEY`
   - Runs `php artisan migrate --force --seed`
   - Caches config/routes/views
   - Sets storage permissions
4. Repoint document roots:
   - `app.31is.com` → `~/31is-site-builder/web/public/`
   - `*.31is.com` → `~/31is-site-builder/web/public/`
   - `31is.com` apex → same (so apex redirects to `app.31is.com`)
   On cPanel this is usually done via **Domains → Document Root** or a symlink (`ln -s ~/31is-site-builder/web/public ~/public_html-31is`). If your cPanel won't allow that, we'll fall back to a one-line `.htaccess` redirect.
5. Smoke test:
   - `curl -I http://app.31is.com/login` → 200
   - Register, create a `test123` site, submit a lead
   - `curl http://test123.31is.com/` → 200, lead in dashboard

## Phase 3 — verify it actually works for students

Once #2 is done, both of us hit `http://app.31is.com/` from a browser, register an account, and walk the full student flow. Anything that's clunky in production gets a quick fix and a redeploy (`git pull && composer install --no-dev && php artisan migrate`).

## Roll back, if needed

```
cd ~/31is-site-builder && git log --oneline -5      # find a good commit
git checkout <sha>                                  # roll to it
php artisan migrate:rollback                        # if a migration was bad
```

The WordPress backup from Phase 1.1 is your fallback if everything implodes — restore the zip + SQL dump and you're back where you started.
