<?php

namespace App\Providers;

use App\Models\Site;
use App\Policies\SitePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Explicitly register policy mappings.
        // Laravel's auto-discovery normally handles this (App\Models\X →
        // App\Policies\XPolicy), but it fails silently in some production
        // configs once `php artisan config:cache` has run, causing every
        // Gate::authorize() call to default to deny → 403 on edit/leads.
        Gate::policy(Site::class, SitePolicy::class);
    }
}
