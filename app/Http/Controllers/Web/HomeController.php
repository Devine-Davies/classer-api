<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Web\Traits\LoadsPosts;
use App\Models\CloudShare;
use App\Models\CatalogItem;
use App\Http\Resources\Web\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

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
        $disk = Storage::disk('s3');
        $postsBasePath = 'classermedia.com/posts';
        $type = request()->segment(1) === 'blog' ? 'blog' : 'story';
        $mapper = $postsBasePath.'/posts-slug-mapper.txt';
        if ($disk->exists($mapper)) {
            $lines = preg_split('/\r\n|\r|\n/', (string) $disk->get($mapper));
            foreach ($lines as $line) {
                if (! str_contains($line, ':')) {
                    continue;
                }

                $parts = explode(':', $line);
                $parts[1] = trim($parts[1]);
                if ($parts[1] === $slug) {
                    $slug = $parts[0];
                    break;
                }
            }
        }

        $postJson = $postsBasePath.'/'.$slug.'/metadata.json';

        if (! $disk->exists($postJson)) {
            return redirect('/', 301);
        }

        $json = json_decode((string) $disk->get($postJson), true);
        $postType = $json['type'];

        // Redirect if post type doesn't match the requested route
        if ($postType !== $type) {
            return $type === 'story'
                ? redirect('/blog/'.$slug, 301)
                : redirect('/', 301);
        }

        $markdown = (string) $disk->get($postsBasePath.'/'.$slug.'/post.md');
        $markdown = str_replace('{{image-path}}', rtrim($disk->url($postsBasePath.'/'.$slug.'/images'), '/'), $markdown);
        $markdown = str_replace('{{video-path}}', rtrim($disk->url($postsBasePath.'/'.$slug.'/videos'), '/'), $markdown);

        return view('posts.entity', [
            'title' => $json['title'],
            'date' => $json['date'],
            'author' => $json['author'],
            'thumbnail' => $disk->url($postsBasePath.'/'.$slug.'/'.$json['thumbnail']),
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
     * Show the application showcase page.
     */
    public function appShowcase()
    {
        return view('app/index');
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
                'thumbnail' => Storage::disk('s3')->url('classermedia.com/assets/images/products/classer-home/Picture01_thumbnail.jpg'),
                'galleryImage' => Storage::disk('s3')->url('classermedia.com/assets/images/products/classer-home/Picture01.jpg'),
                'label' => 'Classer Home device product shot',
                'aria' => 'View Classer Home device product shot',
            ],
            [
                'thumbnail' => Storage::disk('s3')->url('classermedia.com/assets/images/products/classer-home/Picture02_thumbnail.jpg'),
                'galleryImage' => Storage::disk('s3')->url('classermedia.com/assets/images/products/classer-home/Picture02.jpg'),
                'label' => 'Classer device on tabletop',
                'aria' => 'View Classer device on tabletop',
            ],
            [
                'thumbnail' => Storage::disk('s3')->url('classermedia.com/assets/images/products/classer-home/Picture03_thumbnail.jpg'),
                'galleryImage' => Storage::disk('s3')->url('classermedia.com/assets/images/products/classer-home/Picture03.jpg'),
                'label' => 'Classer desktop app preview',
                'aria' => 'View Classer desktop app preview',
            ],
            [
                'thumbnail' => Storage::disk('s3')->url('classermedia.com/assets/images/products/classer-home/Picture04_thumbnail.jpg'),
                'galleryImage' => Storage::disk('s3')->url('classermedia.com/assets/images/products/classer-home/Picture04.jpg'),
                'label' => 'Classer media browsing interface',
                'aria' => 'View Classer media browsing interface',
            ],
            [
                'thumbnail' => Storage::disk('s3')->url('classermedia.com/assets/images/products/classer-home/Picture05_thumbnail.jpg'),
                'galleryImage' => Storage::disk('s3')->url('classermedia.com/assets/images/products/classer-home/Picture05.jpg'),
                'label' => 'Classer Home device product shot',
                'aria' => 'View Classer Home device product shot',
            ],
        ];

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
