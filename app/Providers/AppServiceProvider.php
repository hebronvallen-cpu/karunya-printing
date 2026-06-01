<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        $forwardedProto = request()->headers->get('x-forwarded-proto');
        $appUrl = (string) config('app.url');

        if (
            str_starts_with($appUrl, 'https://')
            || $forwardedProto === 'https'
            || env('RAILWAY_ENVIRONMENT') !== null
            || env('RAILWAY_PUBLIC_DOMAIN') !== null
        ) {
            URL::forceScheme('https');
        }
    }
}
