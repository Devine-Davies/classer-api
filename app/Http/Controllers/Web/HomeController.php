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

        // Sort the folders by date.
        usort($folders, function ($a, $b) use ($storiesFolder) {
            $storyJsonA = public_path($storiesFolder . '/' . $a . '/story.json');
            $storyJsonB = public_path($storiesFolder . '/' . $b . '/story.json');

            if (!file_exists($storyJsonA) || !file_exists($storyJsonB)) {
                return 0;
            }

            $jsonA = json_decode(file_get_contents($storyJsonA), true);
            $jsonB = json_decode(file_get_contents($storyJsonB), true);

            return strtotime($jsonB['date']) - strtotime($jsonA['date']);
        });

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
                'thumbnail' => url('/') . '/x-stories/' . $folder . $json['thumbnail'],
                'permalink' => url('/') . '/stories/' . $json['slug'],
                'slug' => $json['slug'],
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
        $mapper = public_path('x-stories/stories-slug-mapper.txt');
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
    public function download(Request $request)
    {
        $platform = $request->platform;
        $architecture = $request->architecture;
        if ($platform === 'win') {
            return redirect('https://apps.microsoft.com/detail/9mtw32cfv272');
        }

        if ($platform === 'mac') {
            return redirect('https://x-releases.s3.eu-west-2.amazonaws.com/macOS/'. $architecture .'/Classer.dmg');
        }
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

    /**
     * Action camera matcher.
     */
    public function actionCameraMatcher()
    {
        $systemController = new SystemController();
        $questionnaire = $systemController->loadFromResource('action-camera-questionnaire.dataset.json');
        return view('action-camera-matcher/index', [
            'stories' => $this->getStories(3),
            'questionnaire' => $questionnaire,
        ]);
    }

    /**
     * Privacy policy.
     */
    public function privacyPolicy($isoLanCode)
    {
        $privacyPolicy = public_path('privacy-policy/' . $isoLanCode . '.md');

        if (!file_exists($privacyPolicy)) {
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
    public function howToDeactivate()
    {
        $privacyPolicy = public_path('privacy-policy/' . 'en-gb' . '.md');

        if (!file_exists($privacyPolicy)) {
            abort(404);
        }

        $markdown = file_get_contents($privacyPolicy);
        return view('privacy-policy', [
            'content' => Str::markdown($markdown),
        ]);
    }
}
