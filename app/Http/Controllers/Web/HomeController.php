<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Web\Traits\LoadsPosts;
use App\Models\CloudShare;
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
        return view('welcome', [
            'posts' => $this->getPosts(6),
        ]);
    }

    /**
     * Classer Home page.
     */
    public function classerHome()
    {
        return view('classer-home/classer-home');
    }

    /**
     * Classer Home page (v2).
     */
    public function classerHome2()
    {
        return view('classer-home-2/classer-home-2');
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
