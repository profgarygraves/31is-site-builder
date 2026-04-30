<?php

namespace App\Templates;

/**
 * Curated starter presets for Path B (template-based) sites.
 *
 * Each preset returns a fully-populated template_data array suitable for
 * dropping into a fresh sites row. The student can then edit any field.
 *
 * All presets currently render through the prelaunch_v1 Blade view; we
 * differentiate them by sample copy + brand colors. Adding a new view
 * later (e.g. service_v1) is a simple matter of a new entry here pointing
 * at the new template_id.
 */
class Presets
{
    /** @return array<int, array<string, mixed>> */
    public static function all(): array
    {
        return [
            self::prelaunchProduct(),
            self::saasApp(),
            self::serviceBusiness(),
            self::comingSoonMinimal(),
        ];
    }

    public static function find(string $key): ?array
    {
        foreach (self::all() as $p) {
            if ($p['key'] === $key) {
                return $p;
            }
        }
        return null;
    }

    private static function prelaunchProduct(): array
    {
        return [
            'key' => 'prelaunch-product',
            'title' => 'Pre-launch product',
            'subtitle' => 'A physical product you ship to customers. Replace the sample content with yours.',
            'emoji' => '📦',
            'gradient' => 'linear-gradient(135deg, #ff6a1a, #c4321b)',
            'template_id' => 'prelaunch_v1',
            'template_data' => [
                'brand_name' => 'SCORCH',
                'brand_color_1' => '#ff6a1a',
                'brand_color_2' => '#c4321b',
                'announcement_text' => 'LAUNCHING SOON · JOIN THE WAITLIST',
                'hero' => [
                    'breadcrumb_category' => 'HOT SAUCE',
                    'breadcrumb_variant' => '4-PACK',
                    'title' => 'THE ADDICTIVE HEAT',
                    'cta_text' => 'Notify me',
                    'subline' => '4-pack, 5 fl oz each',
                    'description' => 'Experience the burn you cannot stop craving. Our small-batch artisanal blend balances extreme peppers with rich garlic and cane sugar for complex depth.',
                    'images' => [
                        'https://picsum.photos/seed/scorch1/900/900',
                        'https://picsum.photos/seed/scorch2/900/900',
                        'https://picsum.photos/seed/scorch3/900/900',
                    ],
                    'feature_pills' => [
                        ['icon' => '🔥', 'label' => 'PURE FIRE'],
                        ['icon' => '🌊', 'label' => 'CRAFT WAVE'],
                        ['icon' => '✨', 'label' => 'SWEET HEAT'],
                        ['icon' => '🏆', 'label' => 'RICH DEEP'],
                    ],
                    'accordion' => [
                        ['title' => 'Ingredients', 'body' => 'Scorpion peppers, ghost peppers, cane sugar, fresh garlic, sea salt, apple cider vinegar.'],
                        ['title' => 'Benefits', 'body' => 'All-natural, no preservatives, vegan, gluten-free.'],
                        ['title' => 'Directions for use', 'body' => 'Start with one drop. Build from there. Refrigerate after opening.'],
                    ],
                ],
                'tagline' => 'Irresistible savory-sweet heat for daring culinary explorers seeking extreme intensity.',
                'what_you_get' => [
                    ['icon' => '🔥', 'title' => 'Punishing Heat', 'body' => 'A wave of capsaicin from scorpion and ghost peppers that announces itself the moment it hits.'],
                    ['icon' => '🍯', 'title' => 'Savory Sweet', 'body' => 'Cane sugar and roasted garlic round out the burn with a complex sweetness.'],
                    ['icon' => '🛠️', 'title' => 'Artisanal Craft', 'body' => 'Small-batch, hand-bottled, never mass-produced. Every batch numbered.'],
                ],
                'comparison' => [
                    'us_label' => 'SCORCH',
                    'them_label' => 'Typical hot sauce',
                    'rows' => [
                        ['feature' => 'Heat profile', 'us' => 'Layered, builds slowly', 'them' => 'Flat, one-note burn'],
                        ['feature' => 'Ingredients', 'us' => 'Real peppers + cane sugar', 'them' => 'Vinegar + extract'],
                        ['feature' => 'Production', 'us' => 'Small-batch, hand-bottled', 'them' => 'Mass-produced'],
                        ['feature' => 'Founder', 'us' => 'Knows you by name', 'them' => 'Investor PowerPoint'],
                    ],
                ],
                'stat' => [
                    'percent' => '82',
                    'suffix' => '%',
                    'claim' => 'of hot sauce lovers say they crave more variety in heat profiles than what is on the shelf today.',
                ],
                'faq' => [
                    ['question' => 'When will it ship?', 'answer' => 'We are targeting late summer. Waitlist members get first access and 20% off.'],
                    ['question' => 'How hot is it really?', 'answer' => 'Hotter than sriracha, gentler than pure ghost pepper. Mellow finish.'],
                    ['question' => 'Vegan / gluten-free?', 'answer' => 'Yes to both. No animal products, no gluten, no artificial colors.'],
                    ['question' => 'Do you ship internationally?', 'answer' => 'Not at launch — but it is on the roadmap. Tell us where you are on the waitlist.'],
                    ['question' => 'What if I cannot handle it?', 'answer' => 'Full refund, no questions asked.'],
                ],
                'lead_form' => [
                    'modal_title' => 'Join the Scorch waitlist',
                    'modal_subtitle' => 'Be first to know when we ship — plus 20% off your first 4-pack.',
                    'show_name' => true,
                    'show_phone' => false,
                ],
            ],
        ];
    }

    private static function saasApp(): array
    {
        return [
            'key' => 'saas-app',
            'title' => 'SaaS / app launch',
            'subtitle' => 'A software or web app. Replace the sample content with yours.',
            'emoji' => '🚀',
            'gradient' => 'linear-gradient(135deg, #6366f1, #1e1b4b)',
            'template_id' => 'prelaunch_v1',
            'template_data' => [
                'brand_name' => 'STREAMLINE',
                'brand_color_1' => '#6366f1',
                'brand_color_2' => '#1e1b4b',
                'announcement_text' => 'EARLY ACCESS · JOIN THE BETA',
                'hero' => [
                    'breadcrumb_category' => 'SAAS',
                    'breadcrumb_variant' => 'BETA',
                    'title' => 'THE TASK MANAGER THAT GETS OUT OF YOUR WAY',
                    'cta_text' => 'Get early access',
                    'subline' => 'Free during beta · Launching Q2',
                    'description' => 'Stop fighting your project tool. Streamline auto-organizes your work, surfaces what matters today, and disappears when you are in flow.',
                    'images' => [
                        'https://picsum.photos/seed/saas1/900/900',
                        'https://picsum.photos/seed/saas2/900/900',
                        'https://picsum.photos/seed/saas3/900/900',
                    ],
                    'feature_pills' => [
                        ['icon' => '⚡', 'label' => 'INSTANT'],
                        ['icon' => '🧠', 'label' => 'SMART'],
                        ['icon' => '🔌', 'label' => 'CONNECTED'],
                        ['icon' => '🔒', 'label' => 'PRIVATE'],
                    ],
                    'accordion' => [
                        ['title' => 'How it works', 'body' => 'Connect your calendar and inbox. We surface the right work at the right time, no setup required.'],
                        ['title' => 'Integrations', 'body' => 'Google Calendar, Gmail, Slack, Linear, Notion, GitHub, and 30+ more.'],
                        ['title' => 'Pricing', 'body' => 'Free during beta. After launch: $12/mo personal, $20/seat for teams.'],
                    ],
                ],
                'tagline' => 'Stop managing your work. Start doing it.',
                'what_you_get' => [
                    ['icon' => '⚡', 'title' => 'Instant capture', 'body' => 'Forward an email or drop a Slack message. Streamline files it, links it to projects, and surfaces it when relevant.'],
                    ['icon' => '🧠', 'title' => 'Smart prioritization', 'body' => 'AI ranks your day based on deadlines, dependencies, and your past patterns — without nagging.'],
                    ['icon' => '🔌', 'title' => 'Connects everything', 'body' => 'Calendar, email, Slack, Linear, Notion, GitHub. One pane of glass for actually getting things done.'],
                ],
                'comparison' => [
                    'us_label' => 'STREAMLINE',
                    'them_label' => 'Typical task app',
                    'rows' => [
                        ['feature' => 'Setup time', 'us' => '60 seconds', 'them' => 'Half a day'],
                        ['feature' => 'Where work lives', 'us' => 'Pulled in automatically', 'them' => 'Manually copy-pasted'],
                        ['feature' => 'Daily prioritization', 'us' => 'Auto-ranked by AI', 'them' => 'You drag cards around'],
                        ['feature' => 'Integrations', 'us' => '30+ out of the box', 'them' => 'Zapier and prayers'],
                        ['feature' => 'Notifications', 'us' => 'Only when you need them', 'them' => 'Constant pings'],
                    ],
                ],
                'stat' => [
                    'percent' => '67',
                    'suffix' => '%',
                    'claim' => 'of knowledge workers say their task tool creates more work than it removes. We are fixing that.',
                ],
                'faq' => [
                    ['question' => 'When does the beta open?', 'answer' => 'Rolling invites starting next month. Waitlist position determines order.'],
                    ['question' => 'Will it stay free?', 'answer' => 'Beta is free. Personal plans launch at $12/mo with a generous free tier.'],
                    ['question' => 'What about my data?', 'answer' => 'End-to-end encrypted. We cannot read your work even if we wanted to.'],
                    ['question' => 'Mobile apps?', 'answer' => 'iOS and Android shipping alongside the public launch.'],
                    ['question' => 'Team plans?', 'answer' => 'Yes — $20/seat for teams of 3+ with shared projects and analytics.'],
                ],
                'lead_form' => [
                    'modal_title' => 'Get early access',
                    'modal_subtitle' => 'We are inviting users in waves. Tell us where to send your invite.',
                    'show_name' => true,
                    'show_phone' => false,
                ],
            ],
        ];
    }

    private static function serviceBusiness(): array
    {
        return [
            'key' => 'service-business',
            'title' => 'Service / agency',
            'subtitle' => 'A service business — consulting, freelance, agency. Replace the sample content with yours.',
            'emoji' => '💼',
            'gradient' => 'linear-gradient(135deg, #0ea5e9, #0c4a6e)',
            'template_id' => 'prelaunch_v1',
            'template_data' => [
                'brand_name' => 'NORTHWIND',
                'brand_color_1' => '#0ea5e9',
                'brand_color_2' => '#0c4a6e',
                'announcement_text' => 'NOW BOOKING · LIMITED SLOTS',
                'hero' => [
                    'breadcrumb_category' => 'BRAND DESIGN',
                    'breadcrumb_variant' => 'BOUTIQUE STUDIO',
                    'title' => 'BRANDS THAT FEEL LIKE THE FOUNDER, NOT THE INTERN',
                    'cta_text' => 'Book a call',
                    'subline' => 'Brand identity sprints · 3 weeks · From $8K',
                    'description' => 'A two-person studio for early-stage founders who care that their brand reads as serious. We do logo, identity, and a ready-to-ship landing page in three weeks.',
                    'images' => [
                        'https://picsum.photos/seed/agency1/900/900',
                        'https://picsum.photos/seed/agency2/900/900',
                        'https://picsum.photos/seed/agency3/900/900',
                    ],
                    'feature_pills' => [
                        ['icon' => '🎯', 'label' => 'FOCUSED'],
                        ['icon' => '⚡', 'label' => '3 WEEKS'],
                        ['icon' => '🤝', 'label' => 'HANDS ON'],
                        ['icon' => '✨', 'label' => 'CRAFTED'],
                    ],
                    'accordion' => [
                        ['title' => 'What is included', 'body' => 'Brand strategy session, logo system, color + type system, brand guidelines PDF, and a one-page landing site.'],
                        ['title' => 'Timeline', 'body' => 'Week 1: discovery + strategy. Week 2: design rounds. Week 3: refinement, handoff, launch.'],
                        ['title' => 'Pricing', 'body' => 'Brand sprint $8K. Add a marketing site $5K. We bill 50% upfront.'],
                    ],
                ],
                'tagline' => 'A brand that punches above its weight class — without the agency-budget headache.',
                'what_you_get' => [
                    ['icon' => '🎯', 'title' => 'Strategy first', 'body' => 'We start with a positioning workshop, not a Pinterest board. Your brand has a job — we figure out what before we make it pretty.'],
                    ['icon' => '✨', 'title' => 'Senior-only', 'body' => 'No juniors. Every line of every deliverable is touched by a designer with 10+ years of experience.'],
                    ['icon' => '🚀', 'title' => 'Ship-ready', 'body' => 'You leave with a deployed marketing site and brand assets in every format your team needs. Not a Figma file you have to translate.'],
                ],
                'comparison' => [
                    'us_label' => 'NORTHWIND',
                    'them_label' => 'Typical agency',
                    'rows' => [
                        ['feature' => 'Timeline', 'us' => '3 weeks, fixed', 'them' => '3 months, flexible'],
                        ['feature' => 'Who you talk to', 'us' => 'The two senior designers', 'them' => 'An account manager'],
                        ['feature' => 'Final deliverables', 'us' => 'Brand + live site', 'them' => 'A 60-page PDF'],
                        ['feature' => 'Investment', 'us' => '$8K – $13K', 'them' => '$40K+'],
                        ['feature' => 'Iterations', 'us' => '3 rounds, scoped', 'them' => 'Until you give up'],
                    ],
                ],
                'stat' => [
                    'percent' => '12',
                    'suffix' => '',
                    'claim' => 'founders we have worked with — all funded, all shipped on time, all still in business.',
                ],
                'faq' => [
                    ['question' => 'Do you work outside brand identity?', 'answer' => 'Occasionally — for past clients we extend into product UI or marketing systems. New work, brand only.'],
                    ['question' => 'How do I know if we are a fit?', 'answer' => 'Book a 30-min intro call. We will tell you honestly if we are not the right shop for you.'],
                    ['question' => 'Do you do logos only?', 'answer' => 'Logo-only is $4K, but most clients find the full sprint better value.'],
                    ['question' => 'When can you start?', 'answer' => 'Two weeks out, usually. We take three projects per quarter.'],
                    ['question' => 'Where are you based?', 'answer' => 'Chicago and remote. Most discovery sessions are over Zoom.'],
                ],
                'lead_form' => [
                    'modal_title' => 'Book a discovery call',
                    'modal_subtitle' => '30 minutes, free, zero pitch — just a conversation about whether we are the right fit.',
                    'show_name' => true,
                    'show_phone' => true,
                ],
            ],
        ];
    }

    private static function comingSoonMinimal(): array
    {
        return [
            'key' => 'coming-soon-minimal',
            'title' => 'Coming soon (minimal)',
            'subtitle' => 'Bare minimum: title, tagline, email signup. Mostly empty — fill in just what you need.',
            'emoji' => '🚧',
            'gradient' => 'linear-gradient(135deg, #18181b, #52525b)',
            'template_id' => 'prelaunch_v1',
            'template_data' => [
                'brand_name' => 'YOUR BRAND',
                'brand_color_1' => '#18181b',
                'brand_color_2' => '#52525b',
                'announcement_text' => 'COMING SOON',
                'hero' => [
                    'breadcrumb_category' => '',
                    'breadcrumb_variant' => '',
                    'title' => 'SOMETHING NEW IS COMING',
                    'cta_text' => 'Tell me when',
                    'subline' => '',
                    'description' => 'We are building something we think you will like. Drop your email and we will let you know the moment it is ready.',
                    'images' => [
                        'https://picsum.photos/seed/coming1/900/900',
                    ],
                    'feature_pills' => [],
                    'accordion' => [],
                ],
                'tagline' => '',
                'what_you_get' => [],
                'comparison' => ['us_label' => null, 'them_label' => '', 'rows' => []],
                'stat' => ['percent' => null, 'claim' => ''],
                'faq' => [],
                'lead_form' => [
                    'modal_title' => 'Be first to know',
                    'modal_subtitle' => 'We will email you the moment we launch. No spam, ever.',
                    'show_name' => false,
                    'show_phone' => false,
                ],
            ],
        ];
    }
}
