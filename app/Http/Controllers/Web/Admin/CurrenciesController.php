<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Logging\AppLogger;
use App\Services\CurrencyCatalogService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CurrenciesController extends Controller
{
    public function __construct(
        protected AppLogger $logger,
        private readonly CurrencyCatalogService $currencyCatalogService,
    ) {
        $this->logger->setContext(context: 'AdminCurrenciesController Web');
    }

    public function index(Request $request): Factory|View
    {
        $query = trim((string) $request->query('q', ''));
        $needle = strtolower($query);

        $items = collect($this->currencyCatalogService->getItems())
            ->when($needle !== '', function ($collection) use ($needle) {
                return $collection->filter(function (array $currency) use ($needle): bool {
                    return str_contains(strtolower((string) ($currency['code'] ?? '')), $needle)
                        || str_contains(strtolower((string) ($currency['label'] ?? '')), $needle)
                        || str_contains(strtolower((string) ($currency['countryCode'] ?? '')), $needle)
                        || str_contains(strtolower((string) ($currency['symbol'] ?? '')), $needle);
                });
            })
            ->sortBy('code', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();

        return view('admin.currencies.index', [
            'items' => $items,
            'filters' => [
                'q' => $query,
            ],
        ]);
    }

    public function add(): Factory|View
    {
        return view('admin.currencies.add', [
            'item' => null,
        ]);
    }

    public function create(Request $request): RedirectResponse
    {
        $payload = $this->validatePayload($request);
        $row = $this->currencyCatalogService->create($payload);

        return redirect()
            ->route('admin.currencies.edit', ['currencyRow' => $row])
            ->with('success', 'Currency created successfully.');
    }

    public function edit(int $currencyRow): Factory|View
    {
        $item = $this->currencyCatalogService->getByRow($currencyRow);

        abort_if($item === null, 404);

        return view('admin.currencies.edit', [
            'item' => $item,
        ]);
    }

    public function update(Request $request, int $currencyRow): RedirectResponse
    {
        $payload = $this->validatePayload($request);
        $updated = $this->currencyCatalogService->updateByRow($currencyRow, $payload);

        if (! $updated) {
            return redirect()->route('admin.currencies')->with('error', 'Currency row not found.');
        }

        return redirect()
            ->route('admin.currencies.edit', ['currencyRow' => $currencyRow])
            ->with('success', 'Currency updated successfully.');
    }

    public function destroy(Request $request, int $currencyRow): RedirectResponse
    {
        $request->validate([
            'confirmDelete' => ['required', 'in:DELETE'],
        ], [
            'confirmDelete.in' => 'Please type DELETE to confirm currency deletion.',
        ]);

        $deleted = $this->currencyCatalogService->deleteByRow($currencyRow);

        if (! $deleted) {
            return redirect()->route('admin.currencies')->with('error', 'Currency row not found.');
        }

        return redirect()->route('admin.currencies')->with('success', 'Currency deleted successfully.');
    }

    public function togglePublished(Request $request, int $currencyRow): RedirectResponse
    {
        $item = $this->currencyCatalogService->getByRow($currencyRow);

        if ($item === null) {
            return redirect()->route('admin.currencies')->with('error', 'Currency row not found.');
        }

        $isPublished = ! ((bool) ($item['_is_published'] ?? true));
        $updated = $this->currencyCatalogService->setPublishedByRow($currencyRow, $isPublished);

        if (! $updated) {
            return redirect()->route('admin.currencies')->with('error', 'Failed to update currency status.');
        }

        $redirectUrl = route('admin.currencies', array_merge($request->query(), []));

        return redirect()
            ->to($redirectUrl . '#currency-row-' . $currencyRow)
            ->with('success', $isPublished ? 'Currency published.' : 'Currency unpublished.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatePayload(Request $request): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'size:3', 'regex:/^[A-Za-z]{3}$/'],
            'label' => ['required', 'string', 'max:20'],
            'symbol' => ['required', 'string', 'max:12'],
            'countryCode' => ['required', 'string', 'size:2', 'regex:/^[A-Za-z]{2}$/'],
            'rate' => ['required', 'numeric', 'gt:0'],
            'is_published' => ['required', 'boolean'],
        ]);
    }
}