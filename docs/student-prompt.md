# Build my Startup Weekend website with AI

Copy the prompt below into Claude (claude.ai), ChatGPT, v0.dev, or any AI tool that generates HTML. Replace the **bold** sections with your actual product info, then paste the resulting HTML into the **Paste HTML** card at https://app.31is.com.

---

## The prompt

> Generate a single self-contained HTML file for a one-page pre-launch landing page. The page must:
>
> **Product info I'm filling in:**
> - **Brand name**: _e.g. SCORCH_
> - **What it is** (one sentence): _e.g. A small-batch hot sauce for chili-heads who want flavor, not just heat_
> - **Who it's for**: _e.g. Home cooks who already love spicy food and want something more interesting than what's on the grocery shelf_
> - **The problem it solves**: _e.g. Most hot sauces are flat — pure capsaicin, no flavor. Mine layers heat with sweetness and umami_
> - **What makes it different** (3-5 things): _e.g. Real peppers not extract; small batches; balanced sweet-savory finish; vegan; hand-bottled_
> - **Primary call to action**: _e.g. Notify me when it ships / Join the waitlist / Get early access / Pre-order_
> - **Vibe / color palette**: _e.g. warm orange/red / professional indigo / fresh green / minimal black_
>
> **Required structure** (in this order):
> 1. A top scrolling marquee with a short repeating phrase like "LAUNCHING SOON · JOIN THE WAITLIST"
> 2. A header with the brand wordmark on the left and a CTA button on the right
> 3. A hero section split into two columns on desktop: product imagery on the left (use https://picsum.photos/seed/SOMEWORD/900/900 placeholders), product name + CTA + 4 feature pills + description + an accordion of 3 details on the right
> 4. A bold tagline section on a brand-colored gradient background
> 5. Three "What You Get" cards with icons and descriptions
> 6. A "Why choose [Brand]?" comparison table (Brand vs. Typical alternative, 5 rows)
> 7. A big-stat highlight ("82% of...")
> 8. An FAQ accordion with 5 questions
> 9. A footer with brand wordmark and small links
> 10. A modal lead-capture form with name + email fields (initially hidden, opens when CTA clicked)
>
> **Critical technical requirements** (the site builder enforces these):
> - Single self-contained HTML file. All CSS in a `<style>` block at the top of `<head>`. Inline `style="..."` attributes are also fine.
> - **No external JavaScript libraries** (no jQuery, no React, no CDN scripts). Vanilla `<script>` blocks for tiny things like opening the modal are OK — but assume scripts may be stripped, so the page must look right and the form must be submittable even without JavaScript running.
> - **No external CSS or font links** other than `<link rel="preconnect">` and `<link href="https://fonts.bunny.net/...">`. Don't reference any other CDN.
> - The form must use plain HTML `<form method="POST">` (any action URL — it gets rewritten automatically). Form fields named `name`, `email`, and optionally `phone`.
> - Mobile-first responsive — use CSS Grid + `@media (max-width: 768px)` queries.
> - Modern, polished aesthetic — like Linear, Stripe, or a Y Combinator company. Heavy use of whitespace, large display typography, restrained color palette.
> - Use system fonts: `font-family: -apple-system, BlinkMacSystemFont, "Inter", "Segoe UI", sans-serif;`
>
> Output ONLY the HTML, no commentary. Start with `<!doctype html>`.

---

## After you have HTML

1. Copy the entire HTML output
2. Go to https://app.31is.com and log in (or register)
3. Click **+ New site**
4. Pick a subdomain — your business name in lowercase, dashes only (e.g. `scorch`, `acme-tools`, `myco`)
5. Click the **📋 Paste HTML** card
6. Paste your HTML, click **Save**, tick **Published**, click **Save** again
7. Your site is live at `http://[your-subdomain].31is.com`
8. Test the form! Submit a lead and watch it land in your dashboard

## What gets cleaned up automatically

Whatever HTML you paste, the platform does this for you:
- Strips `<script>` tags (no JavaScript can run — security)
- Rewrites every `<form>` to send submissions to your dashboard + email
- Adds a sandbox to any `<iframe>`
- Strips `javascript:` and `vbscript:` URLs
- Adds a CSP and `noindex` header (your site won't show in Google during the event — that's intentional)

So even if your AI generates something with JavaScript or a form pointing somewhere weird, the platform makes it safe and routes leads to YOU.

## Iterating

Don't love what the AI generated? Just paste a refined prompt back into the AI ("make the colors warmer", "shorten the tagline", "swap section 6 for a testimonial section") and re-paste the new HTML over the old. Re-publish.

## If you'd rather not use AI

Two other paths on https://app.31is.com:

- **🎨 Pick a template** — fill in the blanks on a Dough-style polished landing page. Fastest if you don't have HTML yet.
- **🤖 AI fills it in** — describe your product in one sentence and Claude drafts the entire template for you.
