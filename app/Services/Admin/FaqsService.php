<?php

namespace App\Services\Admin;

use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class FaqsService
{
    /**
     * Build paginated FAQ list for the admin table.
     */
    public function paginate(Request $request): LengthAwarePaginator
    {
        $limit = max(1, min((int) $request->query('limit', 20), 100));
        $search = trim((string) $request->query('q', ''));

        $query = Faq::query()
            ->orderBy('sort_order')
            ->orderByDesc('id');

        if ($search !== '') {
            $like = '%'.$search.'%';
            $query->where(function ($nested) use ($like) {
                $nested
                    ->where('question', 'like', $like)
                    ->orWhere('answer', 'like', $like)
                    ->orWhere('category', 'like', $like);
            });
        }

        return $query->paginate($limit)->appends($request->query());
    }

    /**
     * Get a FAQ by its UID.
     */
    public function getByUid(string $uid): ?Faq
    {
        return Faq::where('uid', $uid)->first();
    }

    /**
     * Create a new FAQ with the provided data.
     */
    public function create(array $data): Faq
    {
        return Faq::create($data);
    }

    /**
     * Update a FAQ with the provided data.
     */
    public function update(array $data): bool
    {
        return Faq::where('uid', $data['uid'])->update($data) > 0;
    }

    /**
     * Publish or unpublish a FAQ.
     */
    public function setPublished(string $uid, bool $isPublished): bool
    {
        return Faq::where('uid', $uid)->update(['is_published' => $isPublished]) > 0;
    }

    /**
     * Delete a FAQ.
     */
    public function delete(string $uid): bool
    {
        return Faq::where('uid', $uid)->delete() > 0;
    }

    /**
     * Get published FAQs formatted for the public site FAQ component.
     *
     * @return Collection<int, array{q: string, a: string, category: string}>
     */
    public function publishedForDisplay(): Collection
    {
        return Faq::query()
            ->published()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['question', 'answer', 'category'])
            ->map(fn (Faq $faq) => [
                'q' => $faq->question,
                'a' => $faq->answer,
                'category' => $faq->category ?? '',
            ])
            ->values();
    }
}
