<?php

namespace App\Providers;

use App\Services\Admin\FaqsService;
use App\Services\CloudShareCleanupService;
use App\Services\S3PresignService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The SVG icons loaded from the JSON file.
     *
     * @var array<string, string>|null
     */
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

        // Provide published FAQs only when the public FAQ partial renders.
        // Using a view composer avoids querying the faqs table during console
        // commands and migrations (where the table may not yet exist).
        View::composer('partials.f-a-q', function ($view): void {
            if (! $view->offsetExists('faqs')) {
                $view->with('faqs', app(FaqsService::class)->publishedForDisplay());
            }
        });

        View::share('catalogItemSkus', [
            'PRODUCT-UFI8AM9M',
            'PLAN-GTQVRSBI',
        ]);
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
