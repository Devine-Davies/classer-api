<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CurrencyCatalogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SessionPreferencesController extends Controller
{
    public function __construct(
        private readonly CurrencyCatalogService $currencyCatalogService,
    ) {}

    public function updateCurrency(Request $request): RedirectResponse
    {
        $publishedCodes = $this->currencyCatalogService->getPublishedCodes();

        $validated = $request->validate([
            'currency' => ['required', 'string', Rule::in($publishedCodes)],
        ]);

        $currency = strtolower((string) $validated['currency']);
        $currencyRecord = $this->currencyCatalogService->findByCode($currency);
        $country = strtoupper((string) ($currencyRecord['countryCode'] ?? 'GB'));

        $this->userSession($request)->putMany([
            'checkout.currency' => $currency,
            'checkout.country' => $country,
        ]);

        return back();
    }
}