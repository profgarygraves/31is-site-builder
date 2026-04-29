<?php
/**
 * One-time setup runner for cPanel installs without shell access.
 *
 * Usage:
 *   1. Make sure web/.env exists and SETUP_TOKEN is set to a long random
 *      string.
 *   2. Visit https://your-domain/setup.php?t=YOUR_SETUP_TOKEN
 *   3. Read the output. If "DONE", DELETE THIS FILE immediately.
 *
 * What it does (idempotent — safe to re-run):
 *   - generates APP_KEY if missing
 *   - runs database migrations + seeders
 *   - caches config / routes / views (production performance)
 *
 * Why a script instead of `php artisan ...`: GoDaddy reseller cPanel
 * plans often disable shell access. This is the fallback.
 */

declare(strict_types=1);

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Token gate — must match SETUP_TOKEN in .env. Short-circuits before any
// real work if the caller doesn't know the token.
$expected = (string) env('SETUP_TOKEN', '');
$supplied = (string) ($_GET['t'] ?? '');
if ($expected === '' || strlen($expected) < 16 || !hash_equals($expected, $supplied)) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=UTF-8');
    if ($expected === '') {
        echo "SETUP_TOKEN is not set in web/.env (or is too short — minimum 16 chars).\n";
        echo "Add a long random value, then retry with ?t=THAT_VALUE.\n";
    } else {
        echo "Forbidden. Provide ?t=YOUR_SETUP_TOKEN.\n";
    }
    exit;
}

// Run setup tasks
header('Content-Type: text/plain; charset=UTF-8');
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$tasks = [
    ['key:generate', ['--force' => true]],
    ['migrate',      ['--force' => true]],
    ['db:seed',      ['--force' => true]],
    ['config:cache', []],
    ['route:cache',  []],
    ['view:cache',   []],
];

foreach ($tasks as [$cmd, $args]) {
    echo "==> {$cmd}\n";
    try {
        $code = $kernel->call($cmd, $args);
        echo $kernel->output();
        if ($code !== 0) {
            echo "(exit {$code})\n";
        }
    } catch (\Throwable $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString() . "\n";
    }
    echo "\n";
}

echo "==> DONE\n";
echo "Delete web/public/setup.php now (File Manager → right-click → Delete).\n";
