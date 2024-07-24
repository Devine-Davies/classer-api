<?php

namespace App\Http\Controllers\Web;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Http\Controllers\SystemController;

class HomeController extends Controller
{
    /**
     * Get the post form the posts folder.
     */
    private function getStories($max = 100)
    {
        $storiesFolder = 'x-stories';
        $stories = [];

        if (!is_dir(public_path($storiesFolder))) {
            return [];
        }

        $folders = scandir(public_path($storiesFolder));
        foreach ($folders as $folder) {
            if (count($stories) === $max) {
                break;
            }

            if ($folder === '.' || $folder === '..') {
                continue;
            }

            $storyJson = public_path($storiesFolder . '/' . $folder . '/story.json');

            if (!file_exists($storyJson)) {
                continue;
            }

            $json = json_decode(file_get_contents($storyJson), true);
            $alt = $json['alt'] ?? $json['title'];

            $stories[] = [
                'title' => $json['title'],
                'date' => $json['date'],
                'alt' => $alt,
                'author' => $json['author'],
                'thumbnail' => url('/') . '/x-stories/' . $folder . '/thumbnail.jpg',
                'permalink' => url('/') . '/stories/' . $folder,
                'slug' => $folder,
            ];
        }

        return $stories;
    }

    /**
     * Show the application welcome screen.
     */
    public function stories()
    {
        return view('stories.entities', [
            'stories' => $this->getStories(),
        ]);
    }

    /**
     * Show the application welcome screen.
     */
    public function story($slug)
    {
        $storiesFolder = 'x-stories';
        $storyJson = public_path($storiesFolder . '/' . $slug . '/story.json');

        if (!file_exists($storyJson)) {
            abort(404);
        }

        $json = json_decode(file_get_contents($storyJson), true);
        $markdown = file_get_contents(public_path($storiesFolder . '/' . $slug . '/story.md'));
        $markdown = str_replace('{{image-path}}', url('/') . '/x-stories/' . $slug . '/images', $markdown);
        $markdown = str_replace('{{video-path}}', url('/') . '/x-stories/' . $slug . '/videos', $markdown);
        return view('stories.entity', [
            'title' => $json['title'],
            'date' => $json['date'],
            'author' => $json['author'],
            'thumbnail' => $json['thumbnail'],
            'content' => Str::markdown($markdown),
        ]);
    }

    /**
     * Download the latest releases.
     */
    public function downloadLatest(Request $request)
    {
        $platform = $request->platform;
        $architecture = $request->architecture;
        $systemController = new SystemController();
        $downloadPath = $systemController->latestReleasesPath(
            $platform,
            $architecture,
        );

        if (!$downloadPath) {
            return redirect('/');
        }

        if (!file_exists($downloadPath)) {
            return redirect('/');
        }

        return response()->download($downloadPath);
    }

    /**
     * Show the application welcome screen.
     */
    public function index()
    {
        return view('welcome', [
            'stories' => $this->getStories(3),
        ]);
    }

    public function privacyPolicy($isoLanCode)
    {
        $privacyPolicy = public_path('privacy-policy/' . $isoLanCode . '.md');

        var_dump(file_exists($privacyPolicy));

        if (!file_exists($privacyPolicy)) {
            echo $privacyPolicy;
            abort(404);
        }

        $markdown = file_get_contents($privacyPolicy);
        return view('privacy-policy', [
            'content' => Str::markdown($markdown),
        ]);
    }
}
