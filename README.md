# 31is.com — Site Builder for Startup Weekend

A one-page-website builder for College of DuPage Startup Weekend students. Students sign up, pick a subdomain on `*.31is.com`, choose a starter template (or paste their own HTML), and within minutes have a polished pre-launch landing page that captures lead signups back to the student's dashboard and email.

## Repo layout

```
graves-website-builder/
├── prototype/        Static HTML/CSS reference for the prelaunch_v1 template.
│                     Open prototype/index.html in a browser to view.
├── web/              The Laravel 11 application — this is what gets deployed.
└── .claude/          Local dev-server launch configs.
```

## Local dev

Prereqs: PHP 8.2+, Composer, SQLite (built into macOS).

```bash
cd web
composer install
cp .env.example .env       # edit if needed
php artisan key:generate
php artisan migrate --seed
php artisan serve --host=0.0.0.0 --port=8001
```

Then in your browser (the `:8001` port matters):

| URL | What |
|---|---|
| http://app.lvh.me:8001 | Dashboard / login / register |
| http://`<subdomain>`.lvh.me:8001 | A published student site |

`lvh.me` is a public DNS service that resolves `*.lvh.me` to `127.0.0.1`, so wildcard subdomains work locally with no `/etc/hosts` edits.

## Production

Deployed at `app.31is.com` for the dashboard and `*.31is.com` for student sites. See the deploy section in the project plan.

## What works

- Three creation paths: pick a template, ask AI to fill it in, or paste raw HTML
- HTML sanitizer (DOM-walk based) — keeps designs intact, strips scripts and dangerous URL schemes
- Per-site HMAC token blocks cross-site form-action injection
- Lead pipeline — capture form submissions to DB, email the student, dashboard list, CSV export
