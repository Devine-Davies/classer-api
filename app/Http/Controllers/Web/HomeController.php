<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Web\Traits\LoadsPosts;
use App\Models\CloudShare;
use App\Models\CatalogItem;
use App\Http\Resources\Web\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    use LoadsPosts;

    /**
     * Show the application welcome screen.
     */
    public function posts()
    {
        $type = request()->segment(1) === 'blog' ? 'blog' : 'story';

        return view('posts.entities', [
            'posts' => $this->getPosts($type),
            'title' => $type === 'blog' ? 'Blog Posts' : 'Stories',
            'masonryType' => $type === 'blog' ? 'blog-posts' : 'story-posts',
        ]);
    }

    /**
     * Show the application welcome screen.
     */
    public function post($slug)
    {
        $type = request()->segment(1) === 'blog' ? 'blog' : 'story';
        $mapper = public_path('posts/posts-slug-mapper.txt');
        if (file_exists($mapper)) {
            $lines = file($mapper);
            foreach ($lines as $line) {
                $parts = explode(':', $line);
                $parts[1] = trim($parts[1]);
                if ($parts[1] === $slug) {
                    $slug = $parts[0];
                    break;
                }
            }
        }

        $postsFolder = 'posts';
        $postJson = public_path($postsFolder.'/'.$slug.'/metadata.json');

        if (! file_exists($postJson)) {
            return redirect('/', 301);
        }

        $json = json_decode(file_get_contents($postJson), true);
        $postType = $json['type'];

        // Redirect if post type doesn't match the requested route
        if ($postType !== $type) {
            return $type === 'story'
                ? redirect('/blog/'.$slug, 301)
                : redirect('/', 301);
        }

        $markdown = file_get_contents(public_path($postsFolder.'/'.$slug.'/post.md'));
        $markdown = str_replace('{{image-path}}', url('/').'/posts/'.$slug.'/images', $markdown);
        $markdown = str_replace('{{video-path}}', url('/').'/posts/'.$slug.'/videos', $markdown);

        return view('posts.entity', [
            'title' => $json['title'],
            'date' => $json['date'],
            'author' => $json['author'],
            'thumbnail' => url('/').'/posts/'.$slug.'/'.$json['thumbnail'],
            'content' => Str::markdown($markdown),
        ]);
    }

    /**
     * Download the latest releases.
     */
    public function download(Request $request)
    {
        $platform = $request->platform;
        $architecture = $request->architecture;

        if ($platform === 'win') {
            return redirect('https://apps.microsoft.com/detail/9mtw32cfv272');
        }

        if ($platform === 'mac') {
            return redirect('https://x-releases.s3.eu-west-2.amazonaws.com/macOS/'.$architecture.'/Classer.dmg');
        }

        return view('download');
    }

    /**
     * Guides page
     */
    public function guides()
    {
        return view('guides');
    }

    /**
     * Show the application welcome screen.
     */
    public function index()
    {
        $stories = $this->getPosts('story', 16);
        return view('home.index', [
            'stories' => $stories,
        ]);
    }

    /**
     * About us page.
     */
    public function about()
    {
        return view('about-us');
    }

    /**
     * Contact page.
     */
    public function contact()
    {
        return view('contact');
    }

    /**
     * Show the application subscriptions page.
     */
    public function classerShare()
    {
        return view('classer-share/index');
    }

    /**
     * Show the showcase Classer Home page.
     */
    public function product()
    {
        $catalogSlug = request()->route('catalogSlug');
        $product = CatalogItem::where('slug', $catalogSlug)->firstOrFail();
        $product->load('sellable');

        $gallery = [
            [
                'thumbnail' => 'https://placehold.co/600x400?text=1',
                'galleryImage' => 'https://placehold.co/600x400?text=1',
                'label' => 'Classer Home device product shot',
                'aria' => 'View Classer Home device product shot',
            ],
            [
                'thumbnail' => 'https://placehold.co/600x400?text=2',
                'galleryImage' => 'https://placehold.co/600x400?text=2',
                'label' => 'Classer device on tabletop',
                'aria' => 'View Classer device on tabletop',
            ],
            [
                'thumbnail' => 'https://placehold.co/600x400?text=3',
                'galleryImage' => 'https://placehold.co/600x400?text=3',
                'label' => 'Classer desktop app preview',
                'aria' => 'View Classer desktop app preview',
            ],
            [
                'thumbnail' => 'https://placehold.co/600x400?text=4',
                'galleryImage' => 'https://placehold.co/600x400?text=4',
                'label' => 'Classer media browsing interface',
                'aria' => 'View Classer media browsing interface',
            ],
            [
                'thumbnail' => 'https://placehold.co/600x400?text=5',
                'galleryImage' => 'https://placehold.co/600x400?text=5',
                'label' => 'Classer Home device product shot',
                'aria' => 'View Classer Home device product shot',
            ],
            [
                'thumbnail' => 'https://placehold.co/600x400?text=6',
                'galleryImage' => 'https://placehold.co/600x400?text=6',
                'label' => 'Classer device on tabletop',
                'aria' => 'View Classer device on tabletop',
            ],
            [
                'thumbnail' => 'https://placehold.co/600x400?text=7',
                'galleryImage' => 'https://placehold.co/600x400?text=7',
                'label' => 'Classer desktop app preview',
                'aria' => 'View Classer desktop app preview',
            ],
            [
                // 'thumbnail' => url('/images/placeholders/classer-thumb-8.jpg'),
                'thumbnail' => 'https://placehold.co/600x400?text=8',
                'galleryImage' => 'https://placehold.co/600x400?text=8',
                // 'galleryImage' => url('/images/placeholders/classer-8.jpg'),
                'label' => 'Classer media browsing interface',
                'aria' => 'View Classer media browsing interface',
            ],
        ];

        // Shuffle and return a random-sized subset on every refresh to make UI testing easier.
        $gallery = collect($gallery)
            ->shuffle()
            ->take(random_int(2, count($gallery)))
            ->values()
            ->all();

        $specs = [
            'Dimensions' => '185 × 105 × 85 mm',
            'Connections' => 'USB-C, USB-A, SD card, Ethernet',
            'Storage' => 'Works with your existing external hard drives',
            'App compatibility' => 'Windows and macOS',
            'Power' => 'USB-C power adapter included',
        ];

        $worksWith = [
            'GoPro, DJI, Insta360 and most action camera footage',
            'External hard drives formatted as exFAT',
            'More formats coming through software updates',
        ];

        $stickyProducts = [
            [
                // 'image' => url('/images/placeholders/classer-share-thumb.jpg'),
                'image' => 'https://placehold.net/product-600x600.png',
                'title' => 'Classer Share Cloud',
                'price' => 'FREE',
            ],
            [
                // 'image' => url('/images/placeholders/classer-thumb-1.jpg'),
                'image' => 'https://placehold.net/product-600x600.png',
                'title' => 'Classer Home',
                'price' => '£79',
            ],
        ];

        // check if view exists for the catalog slug, if not, return 404
        if (! view()->exists("products/{$catalogSlug}/index")) {
            return redirect('/'); // Redirect to home page if view doesn't exist
        }

        return view("products/{$catalogSlug}/index", [
            'product' => ProductResource::make($product)->resolve(),
            'gallery' => $gallery,
            'specs' => $specs,
            'worksWith' => $worksWith,
            'stickyProducts' => $stickyProducts,
        ]);
    }

    /**
     * Privacy policy.
     */
    public function privacyPolicy($isoLanCode = null)
    {
        $isoLanCode = $isoLanCode ?? 'en-gb';
        $privacyPolicy = public_path('privacy-policy/'.$isoLanCode.'.md');
        $markdown = file_get_contents($privacyPolicy);

        return view('privacy-policy', [
            'content' => Str::markdown($markdown),
        ]);
    }

    /**
     * How to deactivate.
     */
    public function howToDeactivate()
    {
        $privacyPolicy = public_path('privacy-policy/'.'en-gb'.'.md');

        if (! file_exists($privacyPolicy)) {
            abort(404);
        }

        $markdown = file_get_contents($privacyPolicy);

        return view('privacy-policy', [
            'content' => Str::markdown($markdown),
        ]);
    }

    /**
     * How to deactivate.
     */
    public function shareMoment($uid)
    {
        $entity = CloudShare::where('uid', $uid)->firstOrFail();
        $cloudEntities = $entity->cloudEntities;
        $video = collect($cloudEntities)->firstWhere('type', 'video/mp4');
        $thumbnail = collect($cloudEntities)->firstWhere('type', 'image/jpeg');

        return view('share-moment', [
            'videoSrc' => $video->public_url,
            'thumbnailSrc' => $thumbnail->public_url,
            'entities' => $cloudEntities,
        ]);
    }
}
