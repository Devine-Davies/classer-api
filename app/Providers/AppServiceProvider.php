<?php

namespace App\Providers;

use App\Services\CloudShareCleanupService;
use App\Services\S3PresignService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The FAQs to be shared with all views.
     *
     * @var array<int, array{q: string, a: string, category: string}>
     */
    protected $faqs = [
        [
            'q' => 'What exactly is Classer?',
            'a' => 'Classer is a home device and free desktop app that work together to make the footage on your external hard drives easier to browse, organise and rediscover. The Classer device connects to your external hard drive and home network, while the app gives you a visual interface for exploring the footage stored there.',
            'category' => 'Getting Started',
        ],
        [
            'q' => 'Is Classer available now?',
            'a' => 'Classer is currently available to buy in limited quantities. As a small team, we build and prepare each device in batches. You will see the current estimated dispatch time before completing your order.',
            'category' => 'Ordering',
        ],
        [
            'q' => 'Does Classer include storage?',
            'a' => 'Classer is designed to work with the external storage you already own rather than replacing it. A large internal hard drive is not included unless explicitly stated in the product specifications.',
            'category' => 'Storage',
        ],
        [
            'q' => 'Which external hard drives can I use?',
            'a' => 'Classer is designed to work with standard USB external hard drives and solid-state drives. Connect your drive to the Classer device and the compatible footage stored on it becomes available through the app. Your external drive remains your storage, so you do not need to transfer your footage onto Classer.',
            'category' => 'Storage',
        ],
        [
            'q' => 'Which drive formats are supported?',
            'a' => 'Classer supports external drives formatted as exFAT. If you regularly use the same external drive with both Windows and macOS, exFAT is generally the most compatible option. Classer will not format or reformat your drive. Check the technical specifications before purchasing to confirm that your drive\'s format is supported.',
            'category' => 'Storage',
        ],
        [
            'q' => 'Which video and audio formats are supported?',
            'a' => 'Classer supports common media formats used by action cameras, drones and other recording devices. Supported video formats include MPEG-4/MP4, WebM and Ogg Theora. Supported audio formats include AAC, MP3 and Ogg Vorbis. Compatibility can depend on the codec used inside a file, not only its file extension. Some proprietary, encrypted or unusually encoded files may not be supported.',
            'category' => 'Compatibility',
        ],
        [
            'q' => 'Which cameras are compatible with Classer?',
            'a' => 'Classer works with common video formats produced by many action cameras, DSLR and mirrorless cameras, drones and other recording devices, including compatible footage from brands such as GoPro, DJI, Nikon and others. Features such as location data and other camera information depend on the metadata included in the original file, so availability may vary depending on the camera and file format.',
            'category' => 'Compatibility',
        ],
        [
            'q' => 'What happens if Classer cannot read a file?',
            'a' => 'Unsupported or corrupted files will remain unchanged on your external drive. Classer will not delete or convert them, but they may not appear in the app or may not be available for playback.',
            'category' => 'Compatibility',
        ],
        [
            'q' => 'Does Classer change my existing folders or files?',
            'a' => 'No. Classer reads the compatible footage stored on your connected drive and presents it through a visual interface. Your original folder structure and media files remain where they are. Classer does not reorganise, rename or delete your original files without your instruction.',
            'category' => 'File Management',
        ],
        [
            'q' => 'Does Classer back up my footage?',
            'a' => 'No. Classer helps you browse, organise and rediscover the footage stored on your external drive, but it does not automatically create a second copy of your original files. We recommend keeping at least one additional backup of important footage on another drive or storage service.',
            'category' => 'Backups',
        ],
        [
            'q' => 'Is Classer a cloud service?',
            'a' => 'No. Classer is designed around the storage you already own. Your original footage stays on your connected external hard drives rather than being uploaded to a cloud library. Some optional features, such as sharing, may use temporary cloud services when you choose to use them.',
            'category' => 'Cloud and Network',
        ],
        [
            'q' => 'Do I need an internet connection?',
            'a' => 'You need a home network for the Classer desktop app to communicate with the Classer device.',
            'category' => 'Cloud and Network',
        ],
        [
            'q' => 'How does Classer connect to my home network?',
            'a' => 'Classer connects to your home network using Ethernet. The required Ethernet cable/power cable is included with the Classer device.',
            'category' => 'Cloud and Network',
        ],
        [
            'q' => 'Which computers are supported?',
            'a' => 'The Classer desktop app is available for compatible Windows and macOS computers.',
            'category' => 'Compatibility',
        ],
        [
            'q' => 'Can several people use Classer at the same time?',
            'a' => 'Yes. Multiple people connected to the same home network can access Classer and browse or play footage at the same time. Performance may vary depending on the speed of your home network, the connected external drive, the resolution of the footage and the number of people using Classer simultaneously.',
            'category' => 'Cloud and Network',
        ],
        [
            'q' => 'How do I safely disconnect my external hard drive?',
            'a' => 'Use the eject option in the Classer app before unplugging your external drive. This helps prevent interrupted file operations and reduces the risk of data corruption. Do not disconnect the drive while files are being imported, exported or otherwise processed.',
            'category' => 'Storage',
        ],
        [
            'q' => 'How are Classer software updates handled?',
            'a' => 'Classer periodically receives updates containing improvements, bug fixes and new features. An internet connection may be required to download and install updates.',
            'category' => 'Updates',
        ],
        [
            'q' => 'How can I contact the Classer team?',
            'a' => 'You can contact us at contact@classermedia.com. Classer is built by a small team in Wales, and we read every message ourselves.',
            'category' => 'Support',
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
