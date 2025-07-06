<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Logging\AppLogger;

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
