<?php

namespace App\Providers;

use App\Services\CloudShareCleanupService;
use App\Services\S3PresignService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected static ?array $svgIcons = null;

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
            $expression = self::normalizeIconExpression($icon);

            return "<?php echo \\App\\Providers\\AppServiceProvider::renderIcon({$expression}); ?>";
        });
    }

    public static function renderIcon(mixed $icon): string
    {
        if (! is_string($icon) || $icon === '') {
            return '';
        }

        if (self::$svgIcons === null) {
            $iconsPath = public_path('assets/svg-icons.json');
            $decoded = json_decode(file_get_contents($iconsPath), true);
            self::$svgIcons = is_array($decoded) ? $decoded : [];
        }

        return self::$svgIcons[$icon] ?? '';
    }

    protected static function normalizeIconExpression(string $expression): string
    {
        $expression = trim($expression);

        if ($expression === '') {
            return "''";
        }

        $startsWithQuotedString = str_starts_with($expression, "'") || str_starts_with($expression, '"');
        $looksLikeRuntimeExpression = str_contains($expression, '$')
            || str_contains($expression, '->')
            || str_contains($expression, '[')
            || str_contains($expression, '(')
            || str_contains($expression, '::');

        if ($startsWithQuotedString || $looksLikeRuntimeExpression) {
            return $expression;
        }

        return "'{$expression}'";
    }
}
