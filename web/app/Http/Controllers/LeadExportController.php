<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streams a CSV of all leads for a given site to the student.
 *
 * The lead payload is JSON-shaped (whatever fields the form had), so
 * we collect the union of all keys across leads and emit one column
 * per key plus the metadata columns.
 */
class LeadExportController extends Controller
{
    public function __invoke(Request $request, Site $site): StreamedResponse
    {
        Gate::authorize('update', $site);

        $filename = sprintf('leads-%s-%s.csv', $site->subdomain, now()->format('Ymd-His'));

        return new StreamedResponse(function () use ($site) {
            $out = fopen('php://output', 'w');

            // Build the union of payload keys to keep columns stable.
            $keys = [];
            $site->leads()->orderBy('created_at')->chunk(500, function ($chunk) use (&$keys) {
                foreach ($chunk as $lead) {
                    foreach (array_keys($lead->payload_json ?? []) as $k) {
                        $keys[$k] = true;
                    }
                }
            });
            $payloadKeys = array_keys($keys);

            fputcsv($out, array_merge(
                ['created_at', 'source_form', 'ip_address'],
                $payloadKeys,
                ['user_agent', 'referer']
            ));

            $site->leads()->orderBy('created_at')->chunk(500, function ($chunk) use ($out, $payloadKeys) {
                foreach ($chunk as $lead) {
                    $row = [
                        $lead->created_at?->toIso8601String(),
                        $lead->source_form,
                        $lead->ip_address,
                    ];
                    foreach ($payloadKeys as $k) {
                        $v = $lead->payload_json[$k] ?? '';
                        $row[] = is_array($v) ? json_encode($v) : (string) $v;
                    }
                    $row[] = $lead->user_agent;
                    $row[] = $lead->referer;
                    fputcsv($out, $row);
                }
            });

            fclose($out);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
