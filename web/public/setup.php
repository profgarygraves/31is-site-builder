<?php
/**
 * One-time setup runner for cPanel installs without shell access.
 *
 * Token-gated by SETUP_TOKEN in web/.env. After a successful run,
 * delete this file.
 *
 * We read SETUP_TOKEN directly from the .env file (not via Laravel's
 * env() helper) BEFORE bootstrapping the framework. Reason: any partial
 * bootstrap would mark Application::hasBeenBootstrapped() = true, which
 * then prevents the Console Kernel from running its full bootstrap
 * sequence — and that full bootstrap is what registers the artisan
 * commands we're about to call.
 */

declare(strict_types=1);

$envPath = __DIR__ . '/../.env';

// 1) Token check — read .env directly, no Laravel involved yet.
if (!is_readable($envPath)) {
    http_response_code(503);
    header('Content-Type: text/plain; charset=UTF-8');
    exit("web/.env not found at " . realpath(__DIR__ . '/..') . "/.env\n");
}

$expected = '';
foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (preg_match('/^\s*SETUP_TOKEN\s*=\s*(.+?)\s*$/', $line, $m)) {
        $expected = trim($m[1], "\"'");
        break;
    }
}

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

// 2) Token valid — bootstrap Laravel from scratch and run setup tasks.
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

header('Content-Type: text/plain; charset=UTF-8');

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
echo "Delete web/public/setup.php (and env-check.php) now.\n";
