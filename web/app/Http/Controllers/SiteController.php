<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Services\HtmlSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * Dashboard CRUD for student-owned Sites.
 *
 * All routes here are gated by `auth` middleware. Each site is bound
 * to a user_id; students can only see/edit their own sites.
 */
class SiteController extends Controller
{
    public function index(Request $request)
    {
        $sites = $request->user()
            ->sites()
            ->orderByDesc('updated_at')
            ->withCount('leads')
            ->get();

        return view('sites.index', compact('sites'));
    }

    public function create()
    {
        return view('sites.create');
    }

    public function store(Request $request, HtmlSanitizer $sanitizer): RedirectResponse
    {
        $user = $request->user();

        if ($user->sites()->count() >= \App\Models\User::SITE_LIMIT) {
            return back()->withErrors([
                'subdomain' => 'You have reached the limit of '.\App\Models\User::SITE_LIMIT.' sites.',
            ])->withInput();
        }

        $data = $request->validate([
            'subdomain' => [
                'required',
                'string',
                'min:3',
                'max:32',
                'regex:'.Site::SUBDOMAIN_REGEX,
                Rule::unique('sites', 'subdomain'),
                Rule::notIn(DB::table('reserved_subdomains')->pluck('name')->all()),
            ],
            'source_type' => ['required', Rule::in([Site::SOURCE_HTML, Site::SOURCE_TEMPLATE])],
            'notify_email' => ['required', 'email', 'max:255'],
        ], [
            'subdomain.regex' => 'Subdomain must be lowercase letters, numbers, and dashes (3–32 chars, no leading/trailing dash).',
            'subdomain.not_in' => 'That subdomain is reserved. Please pick another.',
        ]);

        $site = $user->sites()->create([
            'subdomain' => strtolower($data['subdomain']),
            'source_type' => $data['source_type'],
            'notify_email' => $data['notify_email'],
            'is_published' => false,
            'template_id' => $data['source_type'] === Site::SOURCE_TEMPLATE ? 'prelaunch_v1' : null,
        ]);

        return redirect()->route('sites.edit', $site)
            ->with('status', 'Site created. Now add your content and publish.');
    }

    public function edit(Request $request, Site $site)
    {
        Gate::authorize('update', $site);
        return view('sites.edit', compact('site'));
    }

    public function update(Request $request, Site $site, HtmlSanitizer $sanitizer): RedirectResponse
    {
        Gate::authorize('update', $site);

        $data = $request->validate([
            'notify_email' => ['required', 'email', 'max:255'],
            'is_published' => ['nullable', 'boolean'],
            'html_content_raw' => ['nullable', 'string', 'max:512000'],
            'template_data' => ['nullable', 'array'],
        ]);

        $site->notify_email = $data['notify_email'];
        $site->is_published = (bool) $request->input('is_published', false);

        if ($site->source_type === Site::SOURCE_HTML) {
            $raw = $data['html_content_raw'] ?? '';
            $site->html_content_raw = $raw;
            if ($raw !== '') {
                $result = $sanitizer->process($raw, $site);
                $site->html_content = $result['html'];
            } else {
                $site->html_content = '';
            }
        } else {
            $td = $data['template_data'] ?? [];

            // Parse textarea of image URLs (one per line) into array.
            if (isset($td['hero']['images_text'])) {
                $imgs = preg_split('/\r?\n/', (string) $td['hero']['images_text']);
                $td['hero']['images'] = collect($imgs)
                    ->map(fn ($s) => trim((string) $s))
                    ->filter(fn ($s) => filter_var($s, FILTER_VALIDATE_URL))
                    ->values()
                    ->all();
                unset($td['hero']['images_text']);
            }

            // Coerce checkbox-only booleans (unchecked = absent).
            $td['lead_form']['show_name'] = ! empty($td['lead_form']['show_name'] ?? false);
            $td['lead_form']['show_phone'] = ! empty($td['lead_form']['show_phone'] ?? false);

            $site->template_data = $td;
        }

        $site->save();

        return back()->with('status', 'Saved.');
    }

    public function destroy(Request $request, Site $site): RedirectResponse
    {
        Gate::authorize('update', $site);
        $site->delete();
        return redirect()->route('sites.index')->with('status', 'Site deleted.');
    }

    public function show(Request $request, Site $site)
    {
        Gate::authorize('update', $site);
        $leads = $site->leads()->orderByDesc('created_at')->paginate(50);
        return view('sites.leads', compact('site', 'leads'));
    }
}
