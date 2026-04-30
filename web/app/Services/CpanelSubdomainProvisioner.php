<?php

namespace App\Services;

use App\Models\Site;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

/**
 * Auto-provisions cPanel subdomains for student sites by calling cPanel's
 * UAPI directly (the local `uapi` command). Replaces having to add each
 * subdomain manually in cPanel UI when a student creates a site.
 *
 * Why local UAPI instead of HTTP API: the Laravel app runs as the cPanel
 * user (lcfl5uhr556v), so calling `uapi SubDomain addsubdomain ...`
 * authenticates implicitly via the user context — no API token, no
 * cookie session, no auth headers. cPanel-hosted apps are expected to
 * use this pattern.
 *
 * Failure mode is non-blocking: if UAPI returns an error, we log it but
 * the site still gets saved. The student can manually add the subdomain
 * in cPanel as a fallback. The dashboard URL will still 404 until then,
 * but at least site creation never breaks.
 */
class CpanelSubdomainProvisioner
{
    /** Path to cPanel's uapi binary on a typical cPanel host. */
    private const UAPI = '/usr/local/cpanel/bin/uapi';

    /**
     * Create the subdomain `<site->subdomain>.<parent_domain>` and point
     * its document root at the live Laravel public/ directory.
     *
     * Returns true on success, false on failure (caller decides whether
     * to surface the failure to the user). Errors are logged either way.
     */
    public function provision(Site $site): bool
    {
        if (! $this->enabled()) {
            return true; // No-op (e.g. local dev where uapi doesn't exist)
        }

        $rootDomain = config('app.parent_domain');
        $docRoot = config('services.cpanel.public_path');

        if (! $rootDomain || ! $docRoot) {
            Log::warning('CpanelSubdomainProvisioner: parent_domain or public_path not configured');
            return false;
        }

        $result = Process::run([
            self::UAPI,
            '--output=json',
            'SubDomain',
            'addsubdomain',
            'domain='.$site->subdomain,
            'rootdomain='.$rootDomain,
            'dir='.$docRoot,
        ]);

        // Idempotent: if the subdomain already exists, treat as success.
        $stdout = $result->output();
        $decoded = json_decode($stdout, true);

        if ($result->successful() && ($decoded['result']['status'] ?? 0) === 1) {
            Log::info('CpanelSubdomainProvisioner: created subdomain', [
                'subdomain' => $site->subdomain,
                'site_id' => $site->id,
            ]);
            return true;
        }

        // cPanel returns success=0 with errors[] for "already exists" too.
        $errors = $decoded['result']['errors'] ?? [];
        $messages = is_array($errors) ? implode('; ', $errors) : (string) $errors;

        if (str_contains(strtolower($messages), 'already')) {
            Log::info('CpanelSubdomainProvisioner: subdomain already exists, skipping', [
                'subdomain' => $site->subdomain,
            ]);
            return true;
        }

        Log::error('CpanelSubdomainProvisioner: UAPI call failed', [
            'subdomain' => $site->subdomain,
            'exit_code' => $result->exitCode(),
            'stdout' => mb_substr($stdout, 0, 1000),
            'stderr' => mb_substr($result->errorOutput(), 0, 1000),
        ]);
        return false;
    }

    /**
     * Remove the subdomain from cPanel when a site is deleted.
     * Best-effort cleanup — failures are logged but don't block deletion.
     */
    public function deprovision(Site $site): bool
    {
        if (! $this->enabled()) {
            return true;
        }

        $rootDomain = config('app.parent_domain');

        $result = Process::run([
            self::UAPI,
            '--output=json',
            'SubDomain',
            'delsubdomain',
            'domain='.$site->subdomain.'.'.$rootDomain,
        ]);

        if ($result->successful()) {
            Log::info('CpanelSubdomainProvisioner: removed subdomain', ['subdomain' => $site->subdomain]);
            return true;
        }

        Log::warning('CpanelSubdomainProvisioner: deprovision failed (non-fatal)', [
            'subdomain' => $site->subdomain,
            'stderr' => mb_substr($result->errorOutput(), 0, 500),
        ]);
        return false;
    }

    /**
     * Whether auto-provisioning is enabled for this environment. We skip
     * silently in local dev where /usr/local/cpanel/bin/uapi doesn't
     * exist — the developer can still create sites without subdomain
     * entries (their lvh.me wildcard handles the routing).
     */
    private function enabled(): bool
    {
        if (config('services.cpanel.disabled')) {
            return false;
        }
        return is_executable(self::UAPI);
    }
}
