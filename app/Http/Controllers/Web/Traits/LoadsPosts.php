<?php

namespace App\Http\Controllers\Web\Traits;

use Illuminate\Support\Facades\Storage;

trait LoadsPosts
{
    /**
     * Get the post form the posts folder.
     */
    protected function getPosts(?string $type = null, ?int $max = null): array
    {
        $disk = Storage::disk('s3');
        $postsBasePath = 'classermedia.com/posts';
        $posts = [];
        $metadata = [];

        if (! $disk->exists($postsBasePath)) {
            return [];
        }

        $metadataFiles = collect($disk->allFiles($postsBasePath))
            ->filter(fn (string $path): bool => str_ends_with($path, '/metadata.json'))
            ->values();

        foreach ($metadataFiles as $metadataPath) {
            $folder = basename(dirname($metadataPath));
            $json = json_decode((string) $disk->get($metadataPath), true);

            if (! is_array($json) || empty($json['date']) || empty($json['title'])) {
                continue;
            }

            $metadata[$folder] = $json;
        }

        uksort($metadata, function ($a, $b) use ($metadata) {
            return strtotime((string) $metadata[$b]['date']) <=> strtotime((string) $metadata[$a]['date']);
        });

        foreach ($metadata as $folder => $json) {
            if (count($posts) === $max) {
                break;
            }
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
                'thumbnail' => $disk->url($postsBasePath.'/'.$folder.'/'.$json['thumbnail']),
                'permalink' => url('/').'/'.$parentSlug.'/'.$json['slug'],
                'slug' => $json['slug'],
            ];
        }

        return $posts;
    }
}
