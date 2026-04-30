<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Calls Claude (Anthropic Messages API) to draft a fully-populated
 * template_data array for the prelaunch_v1 template, given a short
 * student-supplied description of what they're selling.
 *
 * Implementation notes:
 *   - Uses tool-use (with input_schema matching template_data) so Claude
 *     returns a structured object we can drop straight into the DB.
 *   - Picture URLs come from a public placeholder service since we don't
 *     yet have image upload — students replace these in the editor.
 *   - Falls back gracefully (caller catches the exception and seeds an
 *     empty template) so an API outage never blocks site creation.
 */
class TemplateAiAssist
{
    /**
     * Model ID. Use the bare alias (no date suffix) per Anthropic's docs.
     * `claude-opus-4-7` is the recommended default — best quality for this
     * one-shot generation use case. Override per-environment via the
     * ANTHROPIC_MODEL env if you want to swap to claude-sonnet-4-6 (cheaper)
     * or claude-haiku-4-5 (cheapest, less capable).
     */
    private const DEFAULT_MODEL = 'claude-opus-4-7';
    private const ENDPOINT = 'https://api.anthropic.com/v1/messages';

    public function fillTemplate(string $brief): array
    {
        $apiKey = config('services.anthropic.api_key');
        if (! $apiKey) {
            throw new RuntimeException('ANTHROPIC_API_KEY is not configured in .env (or the config cache is stale — run php artisan config:cache).');
        }

        $model = config('services.anthropic.model', self::DEFAULT_MODEL);

        $payload = [
            'model' => $model,
            'max_tokens' => 8192,
            'system' => $this->systemPrompt(),
            'tools' => [$this->toolDefinition()],
            'tool_choice' => ['type' => 'tool', 'name' => 'fill_landing_page'],
            'messages' => [
                ['role' => 'user', 'content' => "Draft a pre-launch landing page for the following business idea:\n\n{$brief}"],
            ],
        ];

        Log::info('TemplateAiAssist: calling Claude', ['model' => $model, 'brief_length' => mb_strlen($brief)]);

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->timeout(90)->post(self::ENDPOINT, $payload);

        if (! $response->successful()) {
            // Log full body so we can see the actual API error (model not found,
            // rate limit, invalid request, auth, etc.) instead of guessing.
            $body = $response->body();
            Log::error('TemplateAiAssist: Claude API error', [
                'status' => $response->status(),
                'body' => mb_substr($body, 0, 2000),
            ]);
            throw new RuntimeException('Claude API error '.$response->status().': '.mb_substr($body, 0, 500));
        }

        $body = $response->json();
        foreach ($body['content'] ?? [] as $block) {
            if (($block['type'] ?? null) === 'tool_use' && ($block['name'] ?? null) === 'fill_landing_page') {
                return $this->normalize($block['input'] ?? []);
            }
        }

        Log::error('TemplateAiAssist: no tool_use block in response', ['body' => mb_substr(json_encode($body), 0, 2000)]);
        throw new RuntimeException('Claude responded without the expected fill_landing_page tool call.');
    }

    private function systemPrompt(): string
    {
        return <<<PROMPT
You are helping a Startup Weekend student draft a polished pre-launch landing page for an idea they conceived this weekend. The page is single-purpose: validate demand by collecting waitlist signups before the product is built.

Tone:
- Confident but honest. No fake urgency, no "limited time" pressure.
- Write the way a small founder would speak — concrete, specific, light on adjectives.
- Avoid generic SaaS-speak ("revolutionize," "synergy," "world-class").

Structure rules:
- Hero title is the product/service one-liner. Short, declarative, all caps.
- The "tagline" is a sentence-long value statement in the same style.
- Feature pills are 4 short labels (1-2 words). Use unicode emoji for icons.
- "What you get" is 3 cards — each a real benefit (not a feature list).
- The comparison table contrasts you against a typical alternative. Be specific, not strawman.
- The stat is a real-feeling figure that supports the value prop. If you can't justify a number, leave it as null.
- FAQ has 4-6 honest questions a real prospect would ask.
- Suggest 4 image search terms (you don't supply images, just hints) by leaving the existing placeholder URLs alone.

Brand color guidance:
- Pick a color pairing that matches the product category. Food/heat = warm orange/red. SaaS = blue/indigo. Service = teal. Wellness = green. Use #ff6a1a / #c4321b only if hot/warm fits.

Output via the fill_landing_page tool. Don't write a chat reply.
PROMPT;
    }

    private function toolDefinition(): array
    {
        return [
            'name' => 'fill_landing_page',
            'description' => 'Fill in the structured fields for a pre-launch landing page.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['brand_name', 'brand_color_1', 'brand_color_2', 'announcement_text', 'hero', 'tagline', 'what_you_get', 'comparison', 'stat', 'faq', 'lead_form'],
                'properties' => [
                    'brand_name' => ['type' => 'string', 'description' => 'Wordmark — short, all caps usually.'],
                    'brand_color_1' => ['type' => 'string', 'description' => 'Primary hex color, e.g. #ff6a1a'],
                    'brand_color_2' => ['type' => 'string', 'description' => 'Accent (deeper) hex color, e.g. #c4321b'],
                    'announcement_text' => ['type' => 'string', 'description' => 'Looping marquee phrase, e.g. "LAUNCHING SOON · JOIN THE WAITLIST"'],
                    'hero' => [
                        'type' => 'object',
                        'required' => ['title', 'cta_text', 'description', 'feature_pills', 'accordion'],
                        'properties' => [
                            'breadcrumb_category' => ['type' => 'string'],
                            'breadcrumb_variant' => ['type' => 'string'],
                            'title' => ['type' => 'string'],
                            'cta_text' => ['type' => 'string', 'description' => 'e.g. "Notify me", "Join waitlist", "Get early access"'],
                            'subline' => ['type' => 'string'],
                            'description' => ['type' => 'string'],
                            'feature_pills' => [
                                'type' => 'array',
                                'minItems' => 4,
                                'maxItems' => 4,
                                'items' => [
                                    'type' => 'object',
                                    'required' => ['icon', 'label'],
                                    'properties' => [
                                        'icon' => ['type' => 'string', 'description' => 'Single emoji'],
                                        'label' => ['type' => 'string', 'description' => '1-2 words, all caps'],
                                    ],
                                ],
                            ],
                            'accordion' => [
                                'type' => 'array',
                                'minItems' => 3,
                                'maxItems' => 3,
                                'items' => [
                                    'type' => 'object',
                                    'required' => ['title', 'body'],
                                    'properties' => [
                                        'title' => ['type' => 'string'],
                                        'body' => ['type' => 'string'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'tagline' => ['type' => 'string'],
                    'what_you_get' => [
                        'type' => 'array',
                        'minItems' => 3,
                        'maxItems' => 3,
                        'items' => [
                            'type' => 'object',
                            'required' => ['icon', 'title', 'body'],
                            'properties' => [
                                'icon' => ['type' => 'string'],
                                'title' => ['type' => 'string'],
                                'body' => ['type' => 'string'],
                            ],
                        ],
                    ],
                    'comparison' => [
                        'type' => 'object',
                        'required' => ['us_label', 'them_label', 'rows'],
                        'properties' => [
                            'us_label' => ['type' => 'string'],
                            'them_label' => ['type' => 'string'],
                            'rows' => [
                                'type' => 'array',
                                'minItems' => 4,
                                'maxItems' => 5,
                                'items' => [
                                    'type' => 'object',
                                    'required' => ['feature', 'us', 'them'],
                                    'properties' => [
                                        'feature' => ['type' => 'string'],
                                        'us' => ['type' => 'string'],
                                        'them' => ['type' => 'string'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'stat' => [
                        'type' => 'object',
                        'required' => ['percent', 'suffix', 'claim'],
                        'properties' => [
                            'percent' => ['type' => ['string', 'null']],
                            'suffix' => ['type' => 'string'],
                            'claim' => ['type' => 'string'],
                        ],
                    ],
                    'faq' => [
                        'type' => 'array',
                        'minItems' => 4,
                        'maxItems' => 6,
                        'items' => [
                            'type' => 'object',
                            'required' => ['question', 'answer'],
                            'properties' => [
                                'question' => ['type' => 'string'],
                                'answer' => ['type' => 'string'],
                            ],
                        ],
                    ],
                    'lead_form' => [
                        'type' => 'object',
                        'required' => ['modal_title', 'modal_subtitle', 'show_name', 'show_phone'],
                        'properties' => [
                            'modal_title' => ['type' => 'string'],
                            'modal_subtitle' => ['type' => 'string'],
                            'show_name' => ['type' => 'boolean'],
                            'show_phone' => ['type' => 'boolean'],
                        ],
                    ],
                ],
            ],
        ];
    }

    /** Add the placeholder hero images Claude doesn't generate. */
    private function normalize(array $data): array
    {
        $brand = $data['brand_name'] ?? 'preview';
        $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($brand));
        $data['hero']['images'] = [
            "https://picsum.photos/seed/{$slug}1/900/900",
            "https://picsum.photos/seed/{$slug}2/900/900",
            "https://picsum.photos/seed/{$slug}3/900/900",
        ];
        return $data;
    }
}
