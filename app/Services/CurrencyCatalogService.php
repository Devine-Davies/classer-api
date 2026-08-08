<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class CurrencyCatalogService
{
    protected string $path = 'public/currencies.json';

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getItems(): array
    {
        $records = $this->readRecords();

        return array_values(array_map(function (array $record, int $index): array {
            return [
                ...$record,
                '_row' => $index,
                '_is_published' => array_key_exists('is_published', $record)
                    ? (bool) $record['is_published']
                    : true,
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
     * @return array<string, array{label: string, symbol: string, countryCode: string, rate: float, is_published: bool}>
     */
    public function getCurrencyMap(): array
    {
        $map = [];

        foreach ($this->readRecords() as $record) {
            $code = strtolower(trim((string) ($record['code'] ?? '')));

            if ($code === '') {
                continue;
            }

            $map[$code] = [
                'label' => trim((string) ($record['label'] ?? strtoupper($code))),
                'symbol' => trim((string) ($record['symbol'] ?? strtoupper($code))),
                'countryCode' => strtoupper(trim((string) ($record['countryCode'] ?? 'GB'))),
                'rate' => max(0, (float) ($record['rate'] ?? 0)),
                'is_published' => array_key_exists('is_published', $record)
                    ? (bool) $record['is_published']
                    : true,
            ];
        }

        return $map;
    }

    /**
     * @return array<int, array{code: string, label: string, symbol: string, countryCode: string, rate: float}>
     */
    public function getPublishedOptions(): array
    {
        return array_values(array_map(
            function (string $code, array $currency): array {
                return [
                    'code' => $code,
                    'label' => $currency['label'],
                    'symbol' => $currency['symbol'],
                    'countryCode' => $currency['countryCode'],
                    'rate' => $currency['rate'],
                ];
            },
            array_keys(array_filter($this->getCurrencyMap(), fn (array $currency): bool => $currency['is_published'])),
            array_values(array_filter($this->getCurrencyMap(), fn (array $currency): bool => $currency['is_published']))
        ));
    }

    /**
     * @return array<int, string>
     */
    public function getPublishedCodes(): array
    {
        return array_values(array_map(
            static fn (array $currency): string => $currency['code'],
            $this->getPublishedOptions()
        ));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByCode(string $code): ?array
    {
        $currency = $this->getCurrencyMap()[strtolower(trim($code))] ?? null;

        if (! is_array($currency)) {
            return null;
        }

        return [
            'code' => strtolower(trim($code)),
            ...$currency,
        ];
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

        $records[$row] = $this->buildRecord($records[$row], $payload);
        $this->writeRecords($records);

        return true;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function create(array $payload): int
    {
        $records = $this->readRecords();
        $records[] = $this->buildRecord([], $payload);

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

    public function setPublishedByRow(int $row, bool $isPublished): bool
    {
        $records = $this->readRecords();

        if (! isset($records[$row]) || ! is_array($records[$row])) {
            return false;
        }

        $records[$row]['is_published'] = $isPublished;

        $this->writeRecords($records);

        return true;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function readRecords(): array
    {
        if (! Storage::disk('local')->exists($this->path)) {
            return $this->defaultRecords();
        }

        $decoded = json_decode((string) Storage::disk('local')->get($this->path), true);

        if (! is_array($decoded)) {
            return $this->defaultRecords();
        }

        $records = array_values(array_filter($decoded, 'is_array'));

        return empty($records) ? $this->defaultRecords() : $records;
    }

    /**
     * @param  array<int, array<string, mixed>>  $records
     */
    protected function writeRecords(array $records): void
    {
        Storage::disk('local')->put(
            $this->path,
            json_encode(array_values($records), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }

    /**
     * @param  array<string, mixed>  $existingRecord
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function buildRecord(array $existingRecord, array $payload): array
    {
        $code = strtolower(trim((string) ($payload['code'] ?? $existingRecord['code'] ?? 'gbp')));
        $rate = max(0, (float) ($payload['rate'] ?? $existingRecord['rate'] ?? 1));

        return [
            ...$existingRecord,
            'code' => $code,
            'label' => trim((string) ($payload['label'] ?? $existingRecord['label'] ?? strtoupper($code))),
            'symbol' => trim((string) ($payload['symbol'] ?? $existingRecord['symbol'] ?? strtoupper($code))),
            'countryCode' => strtoupper(trim((string) ($payload['countryCode'] ?? $existingRecord['countryCode'] ?? 'GB'))),
            'rate' => $code === 'gbp' ? 1.0 : round($rate, 6),
            'is_published' => array_key_exists('is_published', $payload)
                ? (bool) $payload['is_published']
                : (array_key_exists('is_published', $existingRecord) ? (bool) $existingRecord['is_published'] : true),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function defaultRecords(): array
    {
        return [
            [
                'code' => 'gbp',
                'label' => 'GBP',
                'symbol' => '£',
                'countryCode' => 'GB',
                'rate' => 1.0,
                'is_published' => true,
            ],
            [
                'code' => 'usd',
                'label' => 'USD',
                'symbol' => '$',
                'countryCode' => 'US',
                'rate' => 1.35,
                'is_published' => true,
            ],
            [
                'code' => 'eur',
                'label' => 'EUR',
                'symbol' => '€',
                'countryCode' => 'ES',
                'rate' => 1.17,
                'is_published' => true,
            ],
        ];
    }
}