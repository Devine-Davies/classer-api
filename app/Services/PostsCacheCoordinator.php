<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class PostsCacheCoordinator
{
    public const POSTS_METADATA_CACHE_KEY = 'posts.metadata.all';

    private const POSTS_BASE_PATH = 'classermedia.com/posts';

    private const LOCAL_INDEX_CACHE_PATH = 'admin/posts-index-cache.json';

    /**
     * Return all post metadata, fetched from S3 once and then cached.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getCachedPostsMetadata(): array
    {
        $ttl = (int) config('classer.posts_metadata_cache_ttl_minutes', 60);

        return Cache::remember(
            self::POSTS_METADATA_CACHE_KEY,
            now()->addMinutes($ttl),
            function (): array {
                $disk = Storage::disk('s3');
                $metadata = [];

                if (! $disk->exists(self::POSTS_BASE_PATH)) {
                    return [];
                }

                $metadataFiles = collect($disk->allFiles(self::POSTS_BASE_PATH))
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

    /**
     * Flush shared posts metadata cache.
     */
    public function flushMetadataCache(): void
    {
        Cache::forget(self::POSTS_METADATA_CACHE_KEY);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function readAdminIndexPosts(): array
    {
        if (! $this->localDisk()->exists(self::LOCAL_INDEX_CACHE_PATH)) {
            return [];
        }

        $payload = json_decode((string) $this->localDisk()->get(self::LOCAL_INDEX_CACHE_PATH), true);

        if (! is_array($payload) || ! is_array($payload['posts'] ?? null)) {
            return [];
        }

        return array_values($payload['posts']);
    }

    /**
     * @param  array<int, array<string, mixed>>  $posts
     */
    public function writeAdminIndexPosts(array $posts): void
    {
        $payload = [
            'generated_at' => now()->toIso8601String(),
            'posts' => array_values($posts),
        ];

        $this->localDisk()->put(
            self::LOCAL_INDEX_CACHE_PATH,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    /**
     * @return array{exists: bool, count: int, generated_at: string|null}
     */
    public function adminIndexCacheMeta(): array
    {
        if (! $this->localDisk()->exists(self::LOCAL_INDEX_CACHE_PATH)) {
            return [
                'exists' => false,
                'count' => 0,
                'generated_at' => null,
            ];
        }

        $payload = json_decode((string) $this->localDisk()->get(self::LOCAL_INDEX_CACHE_PATH), true);

        if (! is_array($payload)) {
            return [
                'exists' => false,
                'count' => 0,
                'generated_at' => null,
            ];
        }

        $posts = is_array($payload['posts'] ?? null) ? $payload['posts'] : [];

        return [
            'exists' => true,
            'count' => count($posts),
            'generated_at' => is_string($payload['generated_at'] ?? null) ? $payload['generated_at'] : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $summary
     */
    public function upsertAdminIndexEntry(array $summary): void
    {
        $uid = (string) ($summary['uid'] ?? '');
        if ($uid === '') {
            return;
        }

        $updated = collect($this->readAdminIndexPosts())
            ->reject(fn (array $post): bool => ($post['uid'] ?? null) === $uid)
            ->push($summary)
            ->sortByDesc(fn (array $post): int => strtotime((string) ($post['date'] ?? '')))
            ->values()
            ->all();

        $this->writeAdminIndexPosts($updated);
    }

    public function removeAdminIndexEntry(string $uid): void
    {
        $updated = collect($this->readAdminIndexPosts())
            ->reject(fn (array $post): bool => ($post['uid'] ?? null) === $uid)
            ->values()
            ->all();

        $this->writeAdminIndexPosts($updated);
    }

    private function localDisk()
    {
        return Storage::disk('local');
    }
}
