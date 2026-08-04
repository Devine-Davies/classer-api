<?php

namespace App\Services\Admin;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FaqsService
{
    protected string $path = 'public/faqs.json';

    /**
     * Build paginated FAQ list for the admin table.
     */
    public function paginate(Request $request): LengthAwarePaginator
    {
        $limit = max(1, min((int) $request->query('limit', 20), 100));
        $page = max(1, (int) $request->query('page', 1));
        $search = trim((string) $request->query('q', ''));

        $items = $this->sortedForAdmin($this->all())
            ->when($search !== '', function (Collection $collection) use ($search): Collection {
                $needle = Str::lower($search);

                return $collection->filter(function (object $faq) use ($needle): bool {
                    return Str::contains(Str::lower($faq->question), $needle)
                        || Str::contains(Str::lower($faq->answer), $needle)
                        || Str::contains(Str::lower((string) $faq->category), $needle);
                });
            })
            ->values();

        return new LengthAwarePaginator(
            $items->forPage($page, $limit)->values(),
            $items->count(),
            $limit,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ],
        );
    }

    /**
     * Get a FAQ by its UID.
     */
    public function getByUid(string $uid): ?object
    {
        return $this->all()->firstWhere('uid', $uid);
    }

    /**
     * Create a new FAQ with the provided data.
     */
    public function create(array $data): object
    {
        $now = now();

        $faq = $this->hydrateRecord([
            'uid' => (string) Str::uuid(),
            'question' => (string) $data['question'],
            'answer' => (string) $data['answer'],
            'category' => $data['category'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_published' => (bool) ($data['is_published'] ?? false),
            'created_at' => $now->toIso8601String(),
            'updated_at' => $now->toIso8601String(),
        ]);

        $records = $this->readRecords();
        $records[] = $this->serializeRecord($faq);
        $this->writeRecords($records);

        return $faq;
    }

    /**
     * Update a FAQ with the provided data.
     */
    public function update(array $data): bool
    {
        $records = $this->readRecords();

        foreach ($records as $index => $record) {
            if (($record['uid'] ?? null) !== $data['uid']) {
                continue;
            }

            $records[$index] = [
                ...$record,
                ...collect($data)
                    ->except('uid')
                    ->all(),
                'updated_at' => now()->toIso8601String(),
            ];

            $this->writeRecords($records);

            return true;
        }

        return false;
    }

    /**
     * Publish or unpublish a FAQ.
     */
    public function setPublished(string $uid, bool $isPublished): bool
    {
        return $this->update([
            'uid' => $uid,
            'is_published' => $isPublished,
        ]);
    }

    /**
     * Delete a FAQ.
     */
    public function delete(string $uid): bool
    {
        $records = $this->readRecords();
        $filtered = array_values(array_filter($records, fn (array $record): bool => ($record['uid'] ?? null) !== $uid));

        if (count($filtered) === count($records)) {
            return false;
        }

        $this->writeRecords($filtered);

        return true;
    }

    /**
     * Get published FAQs formatted for the public site FAQ component.
     *
     * @return Collection<int, array{q: string, a: string, category: string}>
     */
    public function publishedForDisplay(): Collection
    {
        return $this->sortedForPublic($this->all())
            ->filter(fn (object $faq): bool => $faq->is_published)
            ->map(fn (object $faq) => [
                'q' => $faq->question,
                'a' => $faq->answer,
                'category' => $faq->category ?? '',
            ])
            ->values();
    }

    /**
     * @return Collection<int, object>
     */
    protected function all(): Collection
    {
        return collect($this->readRecords())
            ->map(fn (array $record): object => $this->hydrateRecord($record))
            ->values();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function readRecords(): array
    {
        if (! Storage::disk('local')->exists($this->path)) {
            return [];
        }

        $decoded = json_decode((string) Storage::disk('local')->get($this->path), true);

        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_filter($decoded, 'is_array'));
    }

    /**
     * @param  array<int, array<string, mixed>>  $records
     */
    protected function writeRecords(array $records): void
    {
        Storage::disk('local')->put(
            $this->path,
            json_encode(array_values($records), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    /**
     * @param  array<string, mixed>  $record
     */
    protected function hydrateRecord(array $record): object
    {
        $createdAt = $this->normalizeDate($record['created_at'] ?? null);
        $updatedAt = $this->normalizeDate($record['updated_at'] ?? null) ?? $createdAt;

        return (object) [
            'uid' => (string) ($record['uid'] ?? Str::uuid()->toString()),
            'question' => (string) ($record['question'] ?? ''),
            'answer' => (string) ($record['answer'] ?? ''),
            'category' => $record['category'] ?? null,
            'sort_order' => (int) ($record['sort_order'] ?? 0),
            'is_published' => (bool) ($record['is_published'] ?? false),
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }

    protected function serializeRecord(object $faq): array
    {
        return [
            'uid' => $faq->uid,
            'question' => $faq->question,
            'answer' => $faq->answer,
            'category' => $faq->category,
            'sort_order' => $faq->sort_order,
            'is_published' => $faq->is_published,
            'created_at' => $faq->created_at?->toIso8601String(),
            'updated_at' => $faq->updated_at?->toIso8601String(),
        ];
    }

    protected function normalizeDate(mixed $value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            return Carbon::parse($value);
        }

        return null;
    }

    /**
     * @param  Collection<int, object>  $items
     * @return Collection<int, object>
     */
    protected function sortedForAdmin(Collection $items): Collection
    {
        return $items->sort(function (object $left, object $right): int {
            $sortCompare = $left->sort_order <=> $right->sort_order;

            if ($sortCompare !== 0) {
                return $sortCompare;
            }

            return ($right->created_at?->getTimestamp() ?? 0) <=> ($left->created_at?->getTimestamp() ?? 0);
        })->values();
    }

    /**
     * @param  Collection<int, object>  $items
     * @return Collection<int, object>
     */
    protected function sortedForPublic(Collection $items): Collection
    {
        return $items->sort(function (object $left, object $right): int {
            $sortCompare = $left->sort_order <=> $right->sort_order;

            if ($sortCompare !== 0) {
                return $sortCompare;
            }

            return ($left->created_at?->getTimestamp() ?? 0) <=> ($right->created_at?->getTimestamp() ?? 0);
        })->values();
    }
}
