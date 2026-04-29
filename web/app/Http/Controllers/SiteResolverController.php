<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Renders a published student site at GET <subdomain>.<parent>/.
 *
 * Routes through ResolveSite middleware which attaches the Site model
 * to the request. Path A serves the pre-sanitized html_content directly;
 * Path B renders the chosen template Blade view with template_data.
 */
class SiteResolverController extends Controller
{
    public function __invoke(Request $request): Response
    {
        /** @var Site $site */
        $site = $request->attributes->get('site');

        if ($site->source_type === Site::SOURCE_HTML) {
            return response($site->html_content ?? '', 200)
                ->header('Content-Type', 'text/html; charset=UTF-8');
        }

        // Path B — render the template view. We restrict to a known list
        // of template ids to prevent template injection.
        $allowed = ['prelaunch_v1'];
        $templateId = in_array($site->template_id, $allowed, true)
            ? $site->template_id
            : 'prelaunch_v1';

        $view = view("templates.{$templateId}", [
            'site' => $site,
            'data' => $this->normalizeTemplateData($site->template_data ?? []),
        ]);

        return response($view->render(), 200)
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }

    /**
     * Apply defaults so the template view never has to defensively
     * check for missing fields. Keeps Blade markup clean.
     */
    private function normalizeTemplateData(array $d): array
    {
        return array_merge([
            'brand_color_1' => '#ff6a1a',
            'brand_color_2' => '#c4321b',
            'brand_name' => 'BRAND',
            'announcement_text' => 'LAUNCHING SOON · JOIN THE WAITLIST',
            'hero' => [
                'images' => [],
                'breadcrumb_category' => 'PRODUCT',
                'breadcrumb_variant' => '',
                'rating' => null,
                'title' => 'Your product title',
                'cta_text' => 'Notify me',
                'subline' => '',
                'feature_pills' => [],
                'description' => '',
                'accordion' => [],
            ],
            'tagline' => '',
            'what_you_get' => [],
            'comparison' => [
                'us_label' => null,
                'them_label' => 'Typical alternative',
                'rows' => [],
            ],
            'stat' => ['percent' => null, 'claim' => ''],
            'faq' => [],
            'footer' => ['contact_email' => null],
            'lead_form' => [
                'show_name' => true,
                'show_phone' => false,
                'modal_title' => 'Join the waitlist',
                'modal_subtitle' => 'Be first to know when we ship.',
            ],
        ], $d);
    }
}
