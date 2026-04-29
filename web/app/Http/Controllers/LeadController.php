<?php

namespace App\Http\Controllers;

use App\Mail\NewLeadNotification;
use App\Models\Lead;
use App\Models\Site;
use App\Services\HtmlSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/**
 * Receives form POSTs from any student site at /__lead/{site}.
 *
 * Validates the per-site token, captures the entire payload as JSON
 * (so any form field on the student's HTML is preserved), queues an
 * email notification to the student, and redirects to /__thanks.
 */
class LeadController extends Controller
{
    public function store(Request $request, HtmlSanitizer $sanitizer): RedirectResponse
    {
        // Read explicitly from the route — the {subdomain} param from
        // Route::domain('{subdomain}.lvh.me') would otherwise shift our
        // positional args.
        $siteId = (int) $request->route('siteId');

        // The URL-supplied site id must match the subdomain-resolved site
        // (set by ResolveSite middleware). This prevents cross-site lead
        // injection — a form on attacker.lvh.me cannot post to /__lead/100
        // and capture leads against site 100.
        /** @var Site|null $resolved */
        $resolved = $request->attributes->get('site');
        if (! $resolved || $resolved->id !== $siteId) {
            abort(404);
        }
        $site = $resolved;

        // Token validation — anti-spam / anti-cross-site abuse.
        $expected = $sanitizer->siteToken($site);
        $supplied = (string) $request->input('_token', '');
        if (! hash_equals($expected, $supplied)) {
            abort(403, 'Invalid form token');
        }

        if (! $site->is_published) {
            abort(404);
        }

        // Capture every submitted field except internal markers.
        $payload = collect($request->except(['_token', '_form_id', '_method']))
            ->map(fn ($v) => is_array($v) ? $v : (string) $v)
            ->all();

        // Hard cap on payload size to prevent abuse.
        $serialized = json_encode($payload);
        if ($serialized === false || strlen($serialized) > 64 * 1024) {
            abort(413, 'Payload too large');
        }

        $lead = Lead::create([
            'site_id' => $site->id,
            'payload_json' => $payload,
            'source_form' => substr((string) $request->input('_form_id', ''), 0, 64) ?: null,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'referer' => substr((string) $request->headers->get('referer', ''), 0, 500),
            'created_at' => now(),
        ]);

        // Email the student. Wrapped in try/catch so a mailer outage
        // doesn't 500 the visitor's submission.
        try {
            Mail::to($site->notify_email)->send(new NewLeadNotification($lead));
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect('/__thanks');
    }
}
