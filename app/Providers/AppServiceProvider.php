<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
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
        Carbon::setLocale((string) config('app.locale', 'id'));
        setlocale(LC_TIME, 'id_ID.UTF-8', 'id_ID', 'id');

        Blade::anonymousComponentPath(resource_path('views/admin/components'));

        // Allow reusable admin component partials to keep their original
        // `components.*` include paths while living under `views/admin`.
        View::addLocation(resource_path('views/admin'));
    }
}
