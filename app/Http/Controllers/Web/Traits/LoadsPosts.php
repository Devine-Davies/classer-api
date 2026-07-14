<?php

namespace App\Http\Controllers\Web\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

trait LoadsPosts
{
    /**
     * Cache key used to store all post metadata fetched from S3.
     * Reference this constant anywhere you need to bust the cache manually.
     */
    public const POSTS_METADATA_CACHE_KEY = 'posts.metadata.all';

    /**
     * Get the post from the posts folder.
     * Results are built from a cached metadata snapshot; S3 is only hit on a
     * cache miss or after an explicit flush.
     */
    protected function getPosts(?string $type = null, ?int $max = null): array
    {
        $metadata = $this->getCachedPostsMetadata();

        $disk = Storage::disk('s3');
        $postsBasePath = 'classermedia.com/posts';
        $posts = [];

        foreach ($metadata as $folder => $json) {
            if ($type && ($json['type'] ?? null) !== $type) {
                continue;
            }

            if ($max !== null && count($posts) === $max) {
                break;
            }

            $parentSlug = $json['type'] === 'story' ? 'stories' : 'blog';

            $posts[] = [
                'title' => $json['title'],
                'description' => $json['description'] ?? '',
                'date' => $json['date'],
                'alt' => $json['alt'] ?? $json['title'],
                'author' => $json['author'],
                'thumbnail' => $disk->url($postsBasePath.'/'.$folder.'/'.$json['thumbnail']),
                'permalink' => url('/').'/'.$parentSlug.'/'.$json['slug'],
                'slug' => $json['slug'],
            ];
        }

        return $posts;
    }

    /**
     * Flush the cached post metadata so the next request re-fetches from S3.
     */
    protected function flushPostsCache(): void
    {
        Cache::forget(self::POSTS_METADATA_CACHE_KEY);
    }

    /**
     * Return all post metadata, fetched from S3 once and then cached.
     *
     * @return array<string, array<string, mixed>>
     */
    private function getCachedPostsMetadata(): array
    {
        $ttl = (int) config('classer.posts_metadata_cache_ttl_minutes', 60);

        return Cache::remember(
            self::POSTS_METADATA_CACHE_KEY,
            now()->addMinutes($ttl),
            function (): array {
                $disk = Storage::disk('s3');
                $postsBasePath = 'classermedia.com/posts';
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

                uksort($metadata, fn ($a, $b) => strtotime((string) $metadata[$b]['date']) <=> strtotime((string) $metadata[$a]['date']));

                return $metadata;
            }
        );
    }
}
