<?php

namespace App\Services\Admin;

use App\Services\PostsCacheCoordinator;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PostsService
{
    private const POSTS_BASE_PATH = 'classermedia.com/posts';

    private const SLUG_MAPPER_PATH = self::POSTS_BASE_PATH.'/posts-slug-mapper.txt';

    public function __construct(
        private readonly PostsCacheCoordinator $cacheCoordinator,
    ) {
    }

    /**
     * Build paginated posts list for the admin posts table.
     */
    public function paginate(Request $request): LengthAwarePaginator
    {
        $limit = max(1, min((int) $request->query('limit', 20), 100));
        $page = max(1, (int) $request->query('page', 1));
        $search = trim((string) $request->query('q', ''));

        $posts = collect($this->cacheCoordinator->readAdminIndexPosts());

        if ($search !== '') {
            $needle = Str::lower($search);

            $posts = $posts->filter(function (array $post) use ($needle): bool {
                $haystack = Str::lower(implode(' ', [
                    $post['uid'],
                    $post['title'],
                    $post['slug'],
                    $post['author'] ?? '',
                    $post['type'] ?? '',
                ]));

                return Str::contains($haystack, $needle);
            });
        }

        $total = $posts->count();
        $items = $posts->forPage($page, $limit)->values()->all();

        return new LengthAwarePaginator(
            $items,
            $total,
            $limit,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    /**
     * Get cache metadata for admin display.
     *
     * @return array{exists: bool, count: int, generated_at: string|null}
     */
    public function indexCacheMeta(): array
    {
        return $this->cacheCoordinator->adminIndexCacheMeta();
    }

    /**
     * Force a full S3 metadata scan and overwrite the local index cache.
     */
    public function refreshIndexCache(): int
    {
        $posts = $this->scanAllPostsSummaries();
        $this->cacheCoordinator->writeAdminIndexPosts($posts);

        // Keep public pages consistent after a manual full rebuild.
        $this->cacheCoordinator->flushMetadataCache();

        return count($posts);
    }

    /**
     * Get a post by its storage folder UID.
     *
     * @return array<string, mixed>|null
     */
    public function getByUid(string $postUid): ?array
    {
        $metadataPath = $this->postMetadataPath($postUid);
        if (! $this->disk()->exists($metadataPath)) {
            return null;
        }

        $json = json_decode((string) $this->disk()->get($metadataPath), true);
        if (! is_array($json)) {
            return null;
        }

        $slugMap = $this->readSlugMap();
        $publicSlug = $slugMap[$postUid] ?? ($json['slug'] ?? $postUid);
        $type = $json['type'] ?? 'blog';
        $parentSlug = $type === 'story' ? 'stories' : 'blog';

        return [
            'uid' => $postUid,
            'title' => $json['title'] ?? '',
            'slug' => $publicSlug,
            'metadataSlug' => $json['slug'] ?? '',
            'type' => $type,
            'date' => $json['date'] ?? '',
            'author' => $json['author'] ?? '',
            'description' => $json['description'] ?? '',
            'thumbnail' => $json['thumbnail'] ?? './thumbnail.jpg',
            'alt' => $json['alt'] ?? ($json['title'] ?? ''),
            'markdown' => $this->disk()->exists($this->postMarkdownPath($postUid))
                ? (string) $this->disk()->get($this->postMarkdownPath($postUid))
                : '',
            'permalink' => url('/').'/'.$parentSlug.'/'.$publicSlug,
        ];
    }

    /**
     * Create a new post structure in S3 and return its data.
     *
     * @param  array{metadata: array<string, mixed>, markdown: string}  $payload
     * @return array<string, mixed>
     */
    public function create(array $payload): array
    {
        $metadata = $this->prepareMetadata($payload['metadata']);
        $this->ensureUniqueSlug($metadata['slug']);

        $uid = (string) Str::uuid();

        $this->disk()->put(
            $this->postMetadataPath($uid),
            json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        $this->disk()->put($this->postMarkdownPath($uid), $payload['markdown']);

        $slugMap = $this->readSlugMap();
        $slugMap[$uid] = $metadata['slug'];
        $this->writeSlugMap($slugMap);
        $this->cacheCoordinator->flushMetadataCache();

        $summary = $this->buildSummaryFromMetadata($uid, $metadata, $metadata['slug']);
        $this->cacheCoordinator->upsertAdminIndexEntry($summary);

        return $this->getByUid($uid) ?? throw ValidationException::withMessages([
            'post' => 'The post was created but could not be read back from storage.',
        ]);
    }

    /**
     * Update an existing post structure in S3 and return its data.
     *
     * @param  array{metadata: array<string, mixed>, markdown: string}  $payload
     * @return array<string, mixed>
     */
    public function update(string $postUid, array $payload): array
    {
        if (! $this->disk()->exists($this->postMetadataPath($postUid))) {
            throw ValidationException::withMessages([
                'post' => 'The selected post could not be found.',
            ]);
        }

        $metadata = $this->prepareMetadata($payload['metadata']);
        $this->ensureUniqueSlug($metadata['slug'], $postUid);

        $this->disk()->put(
            $this->postMetadataPath($postUid),
            json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        $this->disk()->put($this->postMarkdownPath($postUid), $payload['markdown']);

        $slugMap = $this->readSlugMap();
        $slugMap[$postUid] = $metadata['slug'];
        $this->writeSlugMap($slugMap);
        $this->cacheCoordinator->flushMetadataCache();

        $summary = $this->buildSummaryFromMetadata($postUid, $metadata, $metadata['slug']);
        $this->cacheCoordinator->upsertAdminIndexEntry($summary);

        return $this->getByUid($postUid) ?? throw ValidationException::withMessages([
            'post' => 'The post was updated but could not be read back from storage.',
        ]);
    }

    /**
     * Delete a post folder from S3 and remove its slug mapper entry.
     */
    public function delete(string $postUid): void
    {
        if (! $this->disk()->exists($this->postMetadataPath($postUid))) {
            throw ValidationException::withMessages([
                'post' => 'The selected post could not be found.',
            ]);
        }

        $this->disk()->deleteDirectory(self::POSTS_BASE_PATH.'/'.$postUid);

        $slugMap = $this->readSlugMap();
        unset($slugMap[$postUid]);
        $this->writeSlugMap($slugMap);
        $this->cacheCoordinator->flushMetadataCache();

        $this->cacheCoordinator->removeAdminIndexEntry($postUid);
    }

    /**
     * Return all posts formatted for admin views via full S3 scan.
     *
     * @return array<int, array<string, mixed>>
     */
    private function scanAllPostsSummaries(): array
    {
        $slugMap = $this->readSlugMap();

        return collect($this->disk()->allFiles(self::POSTS_BASE_PATH))
            ->filter(fn (string $path): bool => str_ends_with($path, '/metadata.json'))
            ->map(fn (string $path): ?array => $this->buildSummaryFromMetadataPath($path, $slugMap))
            ->filter()
            ->sortByDesc(fn (array $post): int => strtotime((string) $post['date']))
            ->values()
            ->all();
    }

    /**
     * @param  array<string, string>  $slugMap
     * @return array<string, mixed>|null
     */
    private function buildSummaryFromMetadataPath(string $metadataPath, array $slugMap): ?array
    {
        $json = json_decode((string) $this->disk()->get($metadataPath), true);
        if (! is_array($json) || empty($json['title']) || empty($json['date']) || empty($json['type'])) {
            return null;
        }

        $uid = basename(dirname($metadataPath));
        $publicSlug = $slugMap[$uid] ?? ($json['slug'] ?? $uid);

        return $this->buildSummaryFromMetadata($uid, $json, $publicSlug);
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    private function buildSummaryFromMetadata(string $uid, array $metadata, string $publicSlug): array
    {
        $type = $metadata['type'] ?? 'blog';
        $parentSlug = $type === 'story' ? 'stories' : 'blog';

        return [
            'uid' => $uid,
            'title' => (string) ($metadata['title'] ?? ''),
            'slug' => $publicSlug,
            'metadataSlug' => (string) ($metadata['slug'] ?? ''),
            'type' => $type,
            'author' => (string) ($metadata['author'] ?? '-'),
            'date' => (string) ($metadata['date'] ?? ''),
            'dateFormatted' => date('d M Y', strtotime((string) ($metadata['date'] ?? 'now'))),
            'thumbnailUrl' => $this->assetUrl($uid, (string) ($metadata['thumbnail'] ?? './thumbnail.jpg')),
            'permalink' => url('/').'/'.$parentSlug.'/'.$publicSlug,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function readSlugMap(): array
    {
        if (! $this->disk()->exists(self::SLUG_MAPPER_PATH)) {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', (string) $this->disk()->get(self::SLUG_MAPPER_PATH)) ?: [])
            ->filter(fn (string $line): bool => str_contains($line, ':'))
            ->reduce(function (array $carry, string $line): array {
                [$uid, $slug] = array_map('trim', explode(':', $line, 2));
                if ($uid !== '' && $slug !== '') {
                    $carry[$uid] = $slug;
                }

                return $carry;
            }, []);
    }

    /**
     * @param  array<string, string>  $slugMap
     */
    private function writeSlugMap(array $slugMap): void
    {
        ksort($slugMap);

        $contents = collect($slugMap)
            ->map(fn (string $slug, string $uid): string => $uid.': '.$slug)
            ->implode("\n");

        $this->disk()->put(self::SLUG_MAPPER_PATH, $contents);
    }

    private function ensureUniqueSlug(string $slug, ?string $exceptUid = null): void
    {
        $existingOwner = collect($this->readSlugMap())
            ->first(fn (string $mappedSlug, string $uid): bool => $uid !== $exceptUid && Str::lower($mappedSlug) === Str::lower($slug));

        if ($existingOwner !== null) {
            throw ValidationException::withMessages([
                'slug' => 'This public slug is already in use by another post.',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array<string, string>
     */
    private function prepareMetadata(array $metadata): array
    {
        return [
            'title' => trim((string) $metadata['title']),
            'slug' => trim((string) $metadata['slug']),
            'type' => trim((string) $metadata['type']),
            'date' => trim((string) $metadata['date']),
            'author' => trim((string) $metadata['author']),
            'description' => trim((string) ($metadata['description'] ?? '')),
            'thumbnail' => trim((string) ($metadata['thumbnail'] ?? './thumbnail.jpg')),
            'alt' => trim((string) ($metadata['alt'] ?? $metadata['title'])),
        ];
    }

    private function postMetadataPath(string $postUid): string
    {
        return self::POSTS_BASE_PATH.'/'.$postUid.'/metadata.json';
    }

    private function postMarkdownPath(string $postUid): string
    {
        return self::POSTS_BASE_PATH.'/'.$postUid.'/post.md';
    }

    private function assetUrl(string $postUid, string $assetPath): string
    {
        $normalizedPath = ltrim($assetPath, './');

        return $this->disk()->url(self::POSTS_BASE_PATH.'/'.$postUid.'/'.$normalizedPath);
    }

    private function disk()
    {
        return Storage::disk('s3');
    }
}
