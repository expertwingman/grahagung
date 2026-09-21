<?php

namespace App\Providers;

use App\Models\Lead;
use App\Policies\LeadPolicy;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(UrlGenerator $url): void
    {
        // Batas API: 60 request/menit per user (atau per IP kalau belum login).
        RateLimiter::for('api', fn (Request $request) =>
            Limit::perMinute(60)->by($request->user()?->id ?: $request->ip())
        );

        // Policy
        Gate::policy(Lead::class, LeadPolicy::class);

        // Force HTTPS di production (untuk Render)
        if ($this->app->environment('production')) {
            $url->forceScheme('https');
        }
    }
}