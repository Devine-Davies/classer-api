<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Str;

class HomeController extends BaseController
{
    /**
     * Get the post form the posts folder.
     */
    private function getStories()
    {
        $storiesFolder = 'x-stories';
        $stories = [];

        if (!is_dir(public_path($storiesFolder))) {
            return [];
        }

        $folders = scandir(public_path($storiesFolder));
        foreach ($folders as $folder) {
            if (count($stories) === 3) {
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
            $stories[] = [
                'title' => $json['title'],
                'date' => $json['date'],
                'author' => $json['author'],
                'thumbnail' => url()->current() . '/x-stories/' . $folder . '/thumbnail.jpg',
                'permalink' => url()->current() . '/stories/' . $folder,
                'slug' => $folder,
            ];
        }

        return $stories;
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

        $content = file_get_contents(public_path($storiesFolder . '/' . $slug . '/story.md'));
        return view('story', [
            'title' => $json['title'],
            'date' => $json['date'],
            'author' => $json['author'],
            'thumbnail' => $json['thumbnail'],
            'content' => Str::markdown($content),
        ]);
    }

    public function index()
    {
        return view('welcome', [
            'stories' => $this->getStories(),
        ]);
    }
}
