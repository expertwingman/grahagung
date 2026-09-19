<?php

namespace App\Providers;

use App\Models\Lead;
use App\Policies\LeadPolicy;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(UrlGenerator $url): void
    {
        // Policy
        Gate::policy(Lead::class, LeadPolicy::class);

        // Force HTTPS di production (untuk Render)
        if ($this->app->environment('production')) {
            $url->forceScheme('https');
        }
    }
}