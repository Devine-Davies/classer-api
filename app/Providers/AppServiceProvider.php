<?php

namespace App\Providers;

use App\Services\CloudShareCleanupService;
use App\Services\S3PresignService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registering services for dependency injection
        $this->app->singleton(CloudShareCleanupService::class);
        $this->app->singleton(S3PresignService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::directive('icon', function (string $icon): string {
            $icon = trim($icon, " \t\n\r\0\x0B'\"");
            $icons = json_decode(file_get_contents(public_path('assets/svg-icons.json')), true);

            return $icons[$icon] ?? '';
        });
    }
}
