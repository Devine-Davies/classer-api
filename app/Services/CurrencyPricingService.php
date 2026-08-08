<?php

namespace App\Services;

use Illuminate\Http\Request;

class CurrencyPricingService
{
    public function __construct(
        protected CurrencyCatalogService $currencyCatalogService,
    ) {}

    public function baseCurrency(): string
    {
        $configuredCurrency = strtolower(trim((string) config('pricing.base_currency', 'gbp')));

        return array_key_exists($configuredCurrency, $this->currencies())
            ? $configuredCurrency
            : 'gbp';
    }

    public function selectedCurrency(?Request $request = null): string
    {
        $currency = $request?->session()->get('user_context.checkout.currency');

        return $this->normalizeCurrency(is_string($currency) ? $currency : $this->baseCurrency());
    }

    public function normalizeCurrency(?string $currency): string
    {
        $normalized = strtolower(trim((string) $currency));

        return array_key_exists($normalized, $this->currencies())
            ? $normalized
            : $this->baseCurrency();
    }

    public function convert(int $amount, ?string $fromCurrency, ?string $toCurrency): int
    {
        $source = $this->normalizeCurrency($fromCurrency);
        $target = $this->normalizeCurrency($toCurrency);

        if ($source === $target) {
            return max(0, $amount);
        }

        $sourceRate = $this->rate($source);
        $targetRate = $this->rate($target);

        if ($sourceRate <= 0 || $targetRate <= 0) {
            return max(0, $amount);
        }

        $baseAmount = $source === $this->baseCurrency()
            ? $amount
            : $amount / $sourceRate;

        $convertedAmount = $target === $this->baseCurrency()
            ? $baseAmount
            : $baseAmount * $targetRate;

        return max(0, (int) round($convertedAmount));
    }

    public function format(int|string|null $amount, ?string $currency = null): string
    {
        if ($amount === null || $amount === '') {
            return '-';
        }

        $normalizedCurrency = $this->normalizeCurrency($currency);
        $symbol = $this->symbol($normalizedCurrency);
        $formattedAmount = number_format(((int) $amount) / 100, 2, '.', '');

        return $symbol !== null
            ? $symbol . $formattedAmount
            : strtoupper($normalizedCurrency) . ' ' . $formattedAmount;
    }

    /**
     * @return array<string, array{symbol?: string, rate: float|int}>
     */
    public function currencies(): array
    {
        return $this->currencyCatalogService->getCurrencyMap();
    }

    protected function rate(string $currency): float
    {
        return (float) ($this->currencies()[$currency]['rate'] ?? 0);
    }

    protected function symbol(string $currency): ?string
    {
        $symbol = $this->currencies()[$currency]['symbol'] ?? null;

        return is_string($symbol) && $symbol !== '' ? $symbol : null;
    }
}