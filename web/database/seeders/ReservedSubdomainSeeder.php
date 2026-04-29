<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReservedSubdomainSeeder extends Seeder
{
    /**
     * Names that students must NOT be able to claim.
     *
     * These cover (a) infrastructure subdomains we'll use ourselves
     * (app, dashboard, api, mail, www), (b) common cPanel subdomains,
     * and (c) names that look official enough to enable phishing
     * (admin, billing, security, login, postmaster, etc.).
     */
    private const RESERVED = [
        // ours / infra
        'www', 'app', 'admin', 'api', 'dashboard', 'docs', 'status',
        'mail', 'webmail', 'smtp', 'pop', 'imap',
        'cpanel', 'whm', 'ftp', 'ssh',
        'ns1', 'ns2', 'dns',
        'staging', 'dev', 'test', 'demo', 'sandbox',

        // brand-protective
        '31is', 'thirty-one-is', 'thirtyoneis',

        // anti-phishing
        'login', 'signup', 'signin', 'register', 'account', 'accounts',
        'auth', 'sso', 'oauth',
        'billing', 'pay', 'payments', 'checkout', 'invoice',
        'security', 'support', 'help', 'helpdesk', 'service',
        'system', 'root', 'sysadmin',
        'postmaster', 'hostmaster', 'webmaster', 'abuse',

        // common app names that could mislead
        'blog', 'shop', 'store', 'wiki', 'forum', 'community',
    ];

    public function run(): void
    {
        $rows = collect(self::RESERVED)
            ->unique()
            ->map(fn ($name) => [
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->all();

        DB::table('reserved_subdomains')->upsert($rows, ['name']);
    }
}
