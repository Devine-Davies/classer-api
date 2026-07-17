<?php

namespace App\Http\Controllers\Web\Traits;

use App\Services\PostsCacheCoordinator;
use Illuminate\Support\Facades\Storage;

trait LoadsPosts
{
    /**
     * Cache key used to store all post metadata fetched from S3.
     * Reference this constant anywhere you need to bust the cache manually.
     */
    public const POSTS_METADATA_CACHE_KEY = PostsCacheCoordinator::POSTS_METADATA_CACHE_KEY;

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
        app(PostsCacheCoordinator::class)->flushMetadataCache();
    }

    /**
     * Return all post metadata, fetched from S3 once and then cached.
     *
     * @return array<string, array<string, mixed>>
     */
    private function getCachedPostsMetadata(): array
    {
        return app(PostsCacheCoordinator::class)->getCachedPostsMetadata();
    }
}
