<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureRateLimiting();
    }

    /**
     * Rate limits for public, unauthenticated, and AI-driven endpoints.
     * Admin endpoints are authenticated and limited per user, not per IP.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('refund-submit', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));

        RateLimiter::for('order-lookup', fn (Request $request) => Limit::perMinute(15)->by($request->ip()));

        RateLimiter::for('refund-status', fn (Request $request) => Limit::perMinute(30)->by($request->ip()));

        RateLimiter::for('admin-api', fn (Request $request) => Limit::perMinute(120)->by($request->user()?->id ?: $request->ip()));
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
