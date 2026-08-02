<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Logging\AppLogger;
use App\Services\Admin\TutorialsItemsService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TutorialsItemsController extends Controller
{
    public function __construct(
        protected AppLogger $logger,
        private readonly TutorialsItemsService $tutorialsItemsService,
    ) {
        $this->logger->setContext(context: 'AdminTutorialsItemsController Web');
    }

    public function index(): Factory|View
    {
        return view('admin.tutorials-items.index', [
            'items' => $this->tutorialsItemsService->getItems(),
        ]);
    }

    public function add(): Factory|View
    {
        return view('admin.tutorials-items.add', [
            'item' => null,
        ]);
    }

    public function create(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url:http,https'],
            'thumbnail' => ['required', 'url:http,https'],
            'description' => ['nullable', 'string', 'max:2000'],
            'alt' => ['nullable', 'string', 'max:255'],
            'sortOrder' => ['nullable', 'integer', 'min:0'],
        ]);

        $items = $this->tutorialsItemsService->getItems();
        $itemId = str($payload['label'])->slug()->toString();

        if ($itemId === '') {
            $itemId = 'tutorial-'.(count($items) + 1);
        }

        $newItem = [
            'id' => $itemId,
            'label' => $payload['label'],
            'url' => $payload['url'],
            'thumbnail' => $payload['thumbnail'],
            'description' => (string) ($payload['description'] ?? ''),
            'alt' => (string) ($payload['alt'] ?? ''),
            'sortOrder' => (int) ($payload['sortOrder'] ?? 0),
        ];

        $items[] = $newItem;
        $this->tutorialsItemsService->saveItems($items);

        return redirect()->route('admin.tutorials-items.edit', ['itemId' => $itemId])->with('success', 'Tutorial item created successfully.');
    }

    public function edit(string $itemId): Factory|View
    {
        $items = $this->tutorialsItemsService->getItems();
        $item = collect($items)->firstWhere('id', $itemId);

        abort_if($item === null, 404);

        return view('admin.tutorials-items.edit', [
            'item' => $item,
        ]);
    }

    public function update(Request $request, string $itemId): RedirectResponse
    {
        $items = $this->tutorialsItemsService->getItems();
        $index = collect($items)->search(fn (array $item): bool => ($item['id'] ?? '') === $itemId);

        if ($index === false) {
            return redirect()->route('admin.tutorials-items')->with('error', 'Tutorial item not found.');
        }

        $payload = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url:http,https'],
            'thumbnail' => ['required', 'url:http,https'],
            'description' => ['nullable', 'string', 'max:2000'],
            'alt' => ['nullable', 'string', 'max:255'],
            'sortOrder' => ['nullable', 'integer', 'min:0'],
        ]);

        $items[$index] = [
            ...$items[$index],
            'label' => $payload['label'],
            'url' => $payload['url'],
            'thumbnail' => $payload['thumbnail'],
            'description' => (string) ($payload['description'] ?? ''),
            'alt' => (string) ($payload['alt'] ?? ''),
            'sortOrder' => (int) ($payload['sortOrder'] ?? 0),
        ];

        $this->tutorialsItemsService->saveItems($items);

        return redirect()->route('admin.tutorials-items.edit', ['itemId' => $itemId])->with('success', 'Tutorial item updated successfully.');
    }
}
