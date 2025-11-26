<?php

namespace App\Http\Controllers\Web\Traits;

trait LoadsPosts
{
    /**
     * Get the post form the posts folder.
     */
    protected function getPosts(?string $type = null, ?int $max = null): array
    {
        $postsFolder = 'posts';
        $posts = [];

        if (!is_dir(public_path($postsFolder))) {
            return [];
        }

        $folders = scandir(public_path($postsFolder));
        // Sort the folders by date.
        usort($folders, function ($a, $b) use ($postsFolder) {
            $postJsonA = public_path($postsFolder . '/' . $a . '/metadata.json');
            $postJsonB = public_path($postsFolder . '/' . $b . '/metadata.json');
            if (!file_exists($postJsonA) || !file_exists($postJsonB)) {
                return 0;
            }

            $jsonA = json_decode(file_get_contents($postJsonA), true);
            $jsonB = json_decode(file_get_contents($postJsonB), true);

            return strtotime($jsonB['date']) - strtotime($jsonA['date']);
        });

        foreach ($folders as $folder) {
            if (count($posts) === $max) {
                break;
            }

            if ($folder === '.' || $folder === '..') {
                continue;
            }

            $postJson = public_path($postsFolder . '/' . $folder . '/metadata.json');

            if (!file_exists($postJson)) {
                continue;
            }

            $json = json_decode(file_get_contents($postJson), true);
            $alt = $json['alt'] ?? $json['title'];

            if ($type && ($json['type'] ?? null) !== $type) {
                continue;
            }

            $parentSlug = $json['type'] === 'story' ? 'stories' : 'blog';

            $posts[] = [
                'title' => $json['title'],
                'description' => $json['description'] ?? '',
                'date' => $json['date'],
                'alt' => $alt,
                'author' => $json['author'],
                'thumbnail' => url('/') . '/posts/' . $folder . '/' . $json['thumbnail'],
                'permalink' => url('/') . '/'. $parentSlug .'/' . $json['slug'],
                'slug' => $json['slug'],
            ];
        }

        return $posts;
    }
}