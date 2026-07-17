<?php

namespace App\Providers;

use App\Services\CloudShareCleanupService;
use App\Services\S3PresignService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{

    /**
     * The FAQs to be shared with all views.
     *
     * @var array<int, array{q: string, a: string, category: string}>
     */
    protected $faqs = [
        [
            'q' => 'Is it for mobile?',
            'a' => 'Not yet — Classer Home is currently focused on desktop and the home device.',
            'category' => 'Mobile Features',
        ],
        [
            'q' => 'Can I cut and trim my videos?',
            'a' => 'Yes, basic editing features are part of the roadmap.',
            'category' => 'Editing Features',
        ],
        [
            'q' => 'Is this a cloud service?',
            'a' => 'No, Classer Home is local-first. Your footage stays in your home.',
            'category' => 'Cloud & Sync',
        ],
        [
            'q' => 'Does Classer use my directory from my folder file?',
            'a' => 'Yes — Classer reads your existing folder structure and organises on top of it without moving your originals.',
            'category' => 'File Management',
        ],
        [
            'q' => 'Does it work with all action cameras?',
            'a' => 'It supports all major action cameras and standard video formats.',
            'category' => 'Compatibility',
        ],
        [
            'q' => 'I would like to contact the team, how do I do it?',
            'a' => 'Reach out via the contact page or join the early access list to chat with the team directly.',
            'category' => 'Support',
        ],
        [
            'q' => 'How to turn on my GPS on my GoPro?',
            'a' => 'Open your GoPro settings, go to Preferences > Regional > GPS, and switch it on.',
            'category' => 'Guides',
        ],
    ];

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

         View::share('faqs', $this->faqs);
         View::share('catalogItemSkus', [
                'PRODUCT-J3VQXNTI',
                'PLAN-NT8P1DOQ',
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
