<?php

namespace App\Providers;

use App\Services\Admin\FaqsService;
use App\Services\CloudShareCleanupService;
use App\Services\S3PresignService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
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
        // Using a view composer keeps FAQ loading lazy and avoids touching the
        // file-backed content store during unrelated boot paths.
        View::composer('partials.f-a-q', function ($view): void {
            if (! $view->offsetExists('faqs')) {
                $view->with('faqs', app(FaqsService::class)->publishedForDisplay());
            }
        });

        View::share('catalogItemSkus', [
            'PRODUCT-ZYGIMFCL',
            'PLAN-SEY66XRE',
        ]);

        View::share('cloudAssetUrl', static function (?string $path = null, ?string $directoryKey = null): string {
            return self::cloudAssetUrl($path, $directoryKey);
        });
    }

    public static function cloudAssetUrl(?string $path = null, ?string $directoryKey = null): string
    {
        $rawPath = trim((string) $path);

        if ($rawPath === '') {
            return '';
        }

        if (preg_match('/^https?:\/\//i', $rawPath) === 1) {
            return $rawPath;
        }

        $normalizedPath = ltrim($rawPath, '/');

        $disk = (string) config('classer.assets.disk', 's3');
        $configuredDisks = (array) config('filesystems.disks', []);

        if (! array_key_exists($disk, $configuredDisks)) {
            $disk = 's3';
        }

        $basePath = self::resolveCloudAssetBasePath($disk, $directoryKey);
        $objectKey = self::normalizeCloudAssetObjectKey($basePath, $normalizedPath);

        return Storage::disk($disk)->url($objectKey);
    }

    protected static function resolveCloudAssetBasePath(string $disk, ?string $directoryKey = null): string
    {
        $resolvedDirectoryKey = trim((string) ($directoryKey ?? config('classer.assets.directory_key', '')));

        if ($resolvedDirectoryKey !== '') {
            $directories = (array) config("filesystems.disks.{$disk}.directories", []);
            $directoryPath = $directories[$resolvedDirectoryKey] ?? null;

            if (is_string($directoryPath) && trim($directoryPath) !== '') {
                return trim($directoryPath, '/');
            }
        }

        return trim((string) config('classer.assets.base_path', 'classermedia.com'), '/');
    }

    protected static function normalizeCloudAssetObjectKey(string $basePath, string $relativePath): string
    {
        $base = trim($basePath, '/');
        $path = trim($relativePath, '/');

        if ($base === '') {
            return $path;
        }

        if ($path === '' || $path === $base || str_starts_with($path, $base.'/')) {
            return $path === '' ? $base : $path;
        }

        $baseParts = explode('/', $base);
        $pathParts = explode('/', $path);
        $overlap = 0;
        $max = min(count($baseParts), count($pathParts));

        for ($i = $max; $i > 0; $i--) {
            if (array_slice($baseParts, -$i) === array_slice($pathParts, 0, $i)) {
                $overlap = $i;
                break;
            }
        }

        $merged = array_merge($baseParts, array_slice($pathParts, $overlap));

        return implode('/', array_filter($merged, static fn (string $segment): bool => $segment !== ''));
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
