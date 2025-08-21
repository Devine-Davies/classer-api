<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registering services for dependency injection
        $this->app->singleton(\App\Services\CloudShareCleanupService::class);
        $this->app->singleton(\App\Services\S3PresignService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::directive('icon', function (string $icon): string {
            $icons = json_decode(file_get_contents(public_path('assets/svg-icons.json')), true);
            return $icons[$icon];
        });
    }
}
