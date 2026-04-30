# Startup Weekend organizer guide — 31is.com

## What 31is.com is

A one-page-website builder hosted at **app.31is.com**. Students sign up, pick a subdomain (e.g. `scorch.31is.com`), and within a couple minutes have a polished pre-launch landing page that captures email signups back to a dashboard.

Designed for the 54-hour Startup Weekend format: validate demand for a product idea by collecting waitlist signups, without having to actually build the product.

## What students do

Three creation paths — they pick whichever fits:

| Path | What happens | Best for |
|---|---|---|
| **🎨 Pick a template** | Fill in fields (brand name, hero copy, FAQ) on a polished Dough-style template. Sample data pre-filled to overwrite. | Most students. ~5 min start to live. |
| **🤖 AI fills it in** | Describe your product in one sentence. Claude drafts the entire template for them to edit. | Students who don't know what to write yet. |
| **📋 Paste HTML** | Generate HTML elsewhere (Claude, v0, ChatGPT) and paste it. We sanitize it and capture leads from any form. | Students with technical chops or specific design taste. The student-prompt.md doc has a tested prompt. |

All three converge to the same lead pipeline: form submissions land in their dashboard + an email to the address they configured.

## What organizers need to know

### Per-event setup

- **Add subdomains in cPanel** — currently each student needs their subdomain manually added. cPanel rejects literal `*.31is.com` subdomains on this hosting plan; we add named ones (e.g. `mybiz.31is.com`) per student. ~30 sec each.
- **Pre-create accounts (optional)** — you can create student accounts ahead of time at https://app.31is.com/register, or have them register themselves at the event.
- **Distribute the prompt** — the [student-prompt.md](./student-prompt.md) doc has a copy-paste prompt for any AI tool. Put it on the event Slack/wiki.

### Things students will hit

- **"This action is unauthorized"** when clicking Edit/Leads → likely a logged-out session. Have them log out + back in.
- **"Site not reachable" at `<their-subdomain>.31is.com`** → you forgot to add the subdomain in cPanel. Add it (Document Root: `/public_html/31is.com/web/public`), wait 30 sec, retry.
- **No lead emails arriving** → check Resend dashboard for delivery logs. Most likely the address they entered as `notify_email` has a typo, or their inbox provider is filtering.

### What you keep

- **Lead data** — every form submission across every student site lands in our database. After the event, you can pull a CSV of all leads via SSH:
  ```bash
  ssh lcfl5uhr556v@p3plzcpnl445407.prod.phx3.secureserver.net
  cd ~/public_html/31is.com/web
  php artisan tinker
  >>> Lead::with('site')->get()->map(fn($l) => [$l->site->subdomain, $l->payload_json, $l->created_at])->toArray()
  ```
- **Sites & users** — same DB. Useful for follow-up surveys, cohort tracking, etc.

## Tech overview (for someone debugging)

- Stack: Laravel 11, PHP 8.4, MySQL on GoDaddy cPanel
- Email: Resend (with verified `31is.com` domain)
- Repo: https://github.com/profgarygraves/31is-site-builder (public)
- Server path: `/home/lcfl5uhr556v/public_html/31is.com/web/`
- Logs: `/home/lcfl5uhr556v/public_html/31is.com/web/storage/logs/laravel.log`

To deploy a code update:
```bash
ssh lcfl5uhr556v@p3plzcpnl445407.prod.phx3.secureserver.net
~/sync.sh
```
That pulls latest from GitHub, runs migrations, rebuilds caches.

## Known limitations

- **No HTTPS yet** — runs over HTTP. Browsers show "Not Secure" (lock missing). Cloudflare migration would fix this; deferred to post-event.
- **Manual subdomain provisioning** — see "Per-event setup" above.
- **3-site cap per user** — set in `.env` via `USER_SITE_LIMIT`. Default 0 = unlimited.
- **Image upload** — Path B template uses image URLs; no upload button yet. Students paste image URLs from picsum.photos / unsplash / their own hosting.

## Roadmap (post-Startup Weekend)

In rough priority order:

1. **HTTPS via Cloudflare** — free wildcard SSL, 15-min setup
2. **Auto-provision subdomains** — call cPanel UAPI from `SiteController::store` so adding students doesn't need cPanel clicks
3. **Image upload** — direct upload to `storage/app/public/uploads/`, served via a `Storage::url()` symlink
4. **More templates** — service-business, restaurant menu, freelancer portfolio
5. **Per-site analytics** — page views, form-conversion rate
6. **Lead webhooks** — push leads to Zapier / Slack / Google Sheets
