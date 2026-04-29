<?php

use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadExportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SiteResolverController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Host-conditioned routing
|--------------------------------------------------------------------------
|
| Three host classes:
|   1. <parent_domain>            → redirect to app subdomain
|   2. app.<parent_domain>        → dashboard, auth, builder UI
|   3. <anything>.<parent_domain> → student sites + lead capture
|
| Middleware aliases come from bootstrap/app.php.
|
*/

$parent = config('app.parent_domain');
$appSub = config('app.app_subdomain');

/* ===================== Apex redirect ===================== */

Route::domain($parent)->group(function () use ($parent, $appSub) {
    Route::get('/', fn () => redirect(config('app.url_scheme').'://'.$appSub.'.'.$parent));
});

/* ===================== Dashboard / Auth ===================== */

Route::domain($appSub.'.'.$parent)->group(function () {
    Route::get('/', function () {
        return auth()->check()
            ? redirect()->route('sites.index')
            : redirect()->route('login');
    });

    Route::middleware(['auth'])->group(function () {
        Route::redirect('/dashboard', '/sites');

        Route::resource('sites', SiteController::class);
        Route::get('/sites/{site}/leads.csv', LeadExportController::class)
            ->name('sites.leads.csv');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    require __DIR__.'/auth.php';
});

/* ===================== Student sites (catch-all subdomain) ===================== */

Route::domain('{subdomain}.'.$parent)
    ->middleware(['resolve.site', 'inject.csp'])
    ->group(function () {
        Route::get('/', SiteResolverController::class);
        Route::post('/__lead/{siteId}', [LeadController::class, 'store'])->name('lead.store');
        Route::get('/__thanks', fn () => view('public.thanks'))->name('lead.thanks');
    });
