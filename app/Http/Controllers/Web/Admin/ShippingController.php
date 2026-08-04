<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Logging\AppLogger;
use App\Services\Admin\ShippingService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function __construct(
        protected AppLogger $logger,
        private readonly ShippingService $shippingService,
    ) {
        $this->logger->setContext(context: 'AdminShippingController Web');
    }

    public function index(Request $request): Factory|View
    {
        $query = trim((string) $request->query('q', ''));
        $needle = strtolower($query);

        $items = collect($this->shippingService->getItems())
            ->when($needle !== '', function ($collection) use ($needle) {
                return $collection->filter(function (array $country) use ($needle): bool {
                    return str_contains(strtolower((string) ($country['displayName'] ?? '')), $needle)
                        || str_contains(strtolower((string) ($country['countryCode'] ?? '')), $needle)
                        || str_contains(strtolower((string) ($country['rmCountryCode'] ?? '')), $needle)
                        || str_contains(strtolower((string) ($country['postageZone'] ?? '')), $needle);
                });
            })
            ->sortBy('displayName', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();

        return view('admin.shipping.index', [
            'items' => $items,
            'filters' => [
                'q' => $query,
            ],
        ]);
    }

    public function add(): Factory|View
    {
        return view('admin.shipping.add', [
            'item' => null,
        ]);
    }

    public function create(Request $request): RedirectResponse
    {
        $payload = $this->validatePayload($request);

        $row = $this->shippingService->create($payload);

        return redirect()
            ->route('admin.shipping.edit', ['shippingRow' => $row])
            ->with('success', 'Country created successfully.');
    }

    public function edit(int $shippingRow): Factory|View
    {
        $item = $this->shippingService->getByRow($shippingRow);

        abort_if($item === null, 404);

        return view('admin.shipping.edit', [
            'item' => $item,
        ]);
    }

    public function update(Request $request, int $shippingRow): RedirectResponse
    {
        $payload = $this->validatePayload($request);

        $updated = $this->shippingService->updateByRow($shippingRow, $payload);

        if (! $updated) {
            return redirect()->route('admin.shipping')->with('error', 'Shipping row not found.');
        }

        return redirect()
            ->route('admin.shipping.edit', ['shippingRow' => $shippingRow])
            ->with('success', 'Country updated successfully.');
    }

    public function destroy(Request $request, int $shippingRow): RedirectResponse
    {
        $request->validate([
            'confirmDelete' => ['required', 'in:DELETE'],
        ], [
            'confirmDelete.in' => 'Please type DELETE to confirm shipping row deletion.',
        ]);

        $deleted = $this->shippingService->deleteByRow($shippingRow);

        if (! $deleted) {
            return redirect()->route('admin.shipping')->with('error', 'Shipping row not found.');
        }

        return redirect()->route('admin.shipping')->with('success', 'Shipping row deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatePayload(Request $request): array
    {
        return $request->validate([
            'countryCode' => ['required', 'string', 'size:3', 'regex:/^[A-Za-z]{3}$/'],
            'rmCountryCode' => ['required', 'string', 'size:2', 'regex:/^[A-Za-z]{2}$/'],
            'displayName' => ['required', 'string', 'max:255'],
            'postageZone' => ['required', 'string', 'max:120'],
            'is_published' => ['required', 'boolean'],
            'postcodeRegex' => ['nullable', 'string', 'max:255'],
            'shipping_rates' => ['required', 'array', 'min:1'],
            'shipping_rates.*.vendor_id' => ['required', 'string', 'max:16', 'regex:/^[0-9]+$/'],
            'shipping_rates.*.vendor_title' => ['required', 'string', 'max:120'],
            'shipping_rates.*.method' => ['required', 'string', 'max:120'],
            'shipping_rates.*.cost' => ['required', 'integer', 'min:0'],
            'shipping_rates.*.weight_limit' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
