<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\Storage;

class ShippingService
{
    protected string $path = 'public/shipping.json';

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getItems(): array
    {
        $records = $this->readRecords();

        return array_values(array_map(function (array $record, int $index): array {
            $shippingRates = $this->extractShippingRates($record);
            $shipping = $this->extractShippingRate($record);

            return [
                ...$record,
                '_row' => $index,
                '_is_published' => array_key_exists('is_published', $record)
                    ? (bool) $record['is_published']
                    : true,
                '_shipping_rates' => $shippingRates,
                '_shipping_method' => (string) ($shipping['method'] ?? 'Standard'),
                '_shipping_cost' => max(0, (int) ($shipping['cost'] ?? 0)),
                '_shipping_weight_limit' => max(0, (int) ($shipping['weightLimit'] ?? 0)),
            ];
        }, $records, array_keys($records)));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getByRow(int $row): ?array
    {
        $items = $this->getItems();

        return $items[$row] ?? null;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function updateByRow(int $row, array $payload): bool
    {
        $records = $this->readRecords();

        if (! isset($records[$row]) || ! is_array($records[$row])) {
            return false;
        }

        $records[$row] = $this->buildRecord(
            existingRecord: $records[$row],
            payload: $payload,
        );

        $this->writeRecords($records);

        return true;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function create(array $payload): int
    {
        $records = $this->readRecords();

        $records[] = $this->buildRecord(
            existingRecord: [],
            payload: $payload,
        );

        $this->writeRecords($records);

        return count($records) - 1;
    }

    public function deleteByRow(int $row): bool
    {
        $records = $this->readRecords();

        if (! isset($records[$row]) || ! is_array($records[$row])) {
            return false;
        }

        unset($records[$row]);

        $this->writeRecords(array_values($records));

        return true;
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
     * @return array<string, mixed>
     */
    protected function extractShippingRate(array $record): array
    {
        $shippingByVendor = $record['shipping']['1'] ?? null;

        if (! is_array($shippingByVendor)) {
            return [
                'method' => 'Standard',
                'cost' => 0,
                'weightLimit' => 0,
            ];
        }

        $rate = collect($shippingByVendor)
            ->first(function (mixed $item): bool {
                if (! is_array($item)) {
                    return false;
                }

                return strcasecmp((string) ($item['method'] ?? ''), 'Standard') === 0;
            });

        if (! is_array($rate)) {
            $rate = collect($shippingByVendor)->first(fn (mixed $item): bool => is_array($item));
        }

        if (! is_array($rate)) {
            return [
                'method' => 'Standard',
                'cost' => 0,
                'weightLimit' => 0,
            ];
        }

        return $rate;
    }

    /**
     * @param  array<string, mixed>  $existingRecord
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function buildRecord(array $existingRecord, array $payload): array
    {
        $shippingRates = $this->normalizeShippingRates($payload);
        [$shipping, $shippingVendorTitles] = $this->groupShippingRatesByVendor($shippingRates);

        $updated = [
            ...$existingRecord,
            'countryCode' => strtoupper(trim((string) ($payload['countryCode'] ?? $existingRecord['countryCode'] ?? ''))),
            'rmCountryCode' => strtoupper(trim((string) ($payload['rmCountryCode'] ?? $existingRecord['rmCountryCode'] ?? ''))),
            'displayName' => trim((string) ($payload['displayName'] ?? $existingRecord['displayName'] ?? '')),
            'postageZone' => trim((string) ($payload['postageZone'] ?? $existingRecord['postageZone'] ?? '')),
            'is_published' => array_key_exists('is_published', $payload)
                ? (bool) $payload['is_published']
                : (array_key_exists('is_published', $existingRecord) ? (bool) $existingRecord['is_published'] : true),
            'shipping' => $shipping,
        ];

        if (! empty($shippingVendorTitles)) {
            $updated['shippingVendorTitles'] = $shippingVendorTitles;
        } else {
            unset($updated['shippingVendorTitles']);
        }

        $postcodeRegex = trim((string) ($payload['postcodeRegex'] ?? ''));

        if ($postcodeRegex === '') {
            unset($updated['postcodeRegex']);
        } else {
            $updated['postcodeRegex'] = $postcodeRegex;
        }

        return $updated;
    }

    /**
     * @param  array<string, mixed>  $record
     * @return array<int, array{vendor_id: string, vendor_title: string, method: string, cost: int, weight_limit: int}>
     */
    protected function extractShippingRates(array $record): array
    {
        $shipping = is_array($record['shipping'] ?? null) ? $record['shipping'] : [];
        $titles = is_array($record['shippingVendorTitles'] ?? null) ? $record['shippingVendorTitles'] : [];
        $rates = [];

        foreach ($shipping as $vendorId => $vendorRates) {
            if (! is_array($vendorRates)) {
                continue;
            }

            foreach ($vendorRates as $rate) {
                if (! is_array($rate)) {
                    continue;
                }

                $normalizedVendorId = trim((string) $vendorId);

                if ($normalizedVendorId === '') {
                    continue;
                }

                $rates[] = [
                    'vendor_id' => $normalizedVendorId,
                    'vendor_title' => trim((string) ($titles[$normalizedVendorId] ?? ('Vendor '.$normalizedVendorId))),
                    'method' => trim((string) ($rate['method'] ?? 'Standard')),
                    'cost' => max(0, (int) ($rate['cost'] ?? 0)),
                    'weight_limit' => max(0, (int) ($rate['weightLimit'] ?? 0)),
                ];
            }
        }

        if (empty($rates)) {
            $rate = $this->extractShippingRate($record);

            $rates[] = [
                'vendor_id' => '1',
                'vendor_title' => trim((string) ($titles['1'] ?? 'Vendor 1')),
                'method' => trim((string) ($rate['method'] ?? 'Standard')),
                'cost' => max(0, (int) ($rate['cost'] ?? 0)),
                'weight_limit' => max(0, (int) ($rate['weightLimit'] ?? 0)),
            ];
        }

        return array_values($rates);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, array{vendor_id: string, vendor_title: string, method: string, cost: int, weight_limit: int}>
     */
    protected function normalizeShippingRates(array $payload): array
    {
        $rawRates = $payload['shipping_rates'] ?? null;

        if (! is_array($rawRates) || empty($rawRates)) {
            return [[
                'vendor_id' => '1',
                'vendor_title' => 'Vendor 1',
                'method' => 'Standard',
                'cost' => 0,
                'weight_limit' => 0,
            ]];
        }

        $rates = collect($rawRates)
            ->filter(fn (mixed $rate): bool => is_array($rate))
            ->map(function (array $rate): array {
                $vendorId = preg_replace('/[^0-9]/', '', (string) ($rate['vendor_id'] ?? ''));

                if ($vendorId === null || $vendorId === '') {
                    $vendorId = '1';
                }

                $vendorTitle = trim((string) ($rate['vendor_title'] ?? ''));

                if ($vendorTitle === '') {
                    $vendorTitle = 'Vendor '.$vendorId;
                }

                $method = trim((string) ($rate['method'] ?? 'Standard'));

                if ($method === '') {
                    $method = 'Standard';
                }

                return [
                    'vendor_id' => $vendorId,
                    'vendor_title' => $vendorTitle,
                    'method' => $method,
                    'cost' => max(0, (int) ($rate['cost'] ?? 0)),
                    'weight_limit' => max(0, (int) ($rate['weight_limit'] ?? 0)),
                ];
            })
            ->values()
            ->all();

        if (empty($rates)) {
            return [[
                'vendor_id' => '1',
                'vendor_title' => 'Vendor 1',
                'method' => 'Standard',
                'cost' => 0,
                'weight_limit' => 0,
            ]];
        }

        return $rates;
    }

    /**
     * @param  array<int, array{vendor_id: string, vendor_title: string, method: string, cost: int, weight_limit: int}>  $rates
     * @return array{0: array<string, array<int, array{method: string, cost: int, weightLimit: int}>>, 1: array<string, string>}
     */
    protected function groupShippingRatesByVendor(array $rates): array
    {
        $shipping = [];
        $vendorTitles = [];

        foreach ($rates as $rate) {
            $vendorId = trim((string) ($rate['vendor_id'] ?? ''));

            if ($vendorId === '') {
                continue;
            }

            $vendorTitle = trim((string) ($rate['vendor_title'] ?? ''));
            $vendorTitles[$vendorId] = $vendorTitle === '' ? 'Vendor '.$vendorId : $vendorTitle;

            if (! isset($shipping[$vendorId]) || ! is_array($shipping[$vendorId])) {
                $shipping[$vendorId] = [];
            }

            $shipping[$vendorId][] = [
                'method' => trim((string) ($rate['method'] ?? 'Standard')) ?: 'Standard',
                'cost' => max(0, (int) ($rate['cost'] ?? 0)),
                'weightLimit' => max(0, (int) ($rate['weight_limit'] ?? 0)),
            ];
        }

        if (empty($shipping)) {
            $shipping['1'] = [[
                'method' => 'Standard',
                'cost' => 0,
                'weightLimit' => 0,
            ]];
            $vendorTitles['1'] = 'Vendor 1';
        }

        return [$shipping, $vendorTitles];
    }
}
