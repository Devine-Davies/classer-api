<?php

namespace App\Providers;

use App\Logging\AppLogger;
use Illuminate\Support\ServiceProvider;

class LoggerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AppLogger::class, function ($app) {
            $context = $app->runningInConsole() ? 'console' : 'http';

            // You could also conditionally set a different channel here if needed
            $channel = 'app';

            return new AppLogger($context, $channel);
        });
    }
}
