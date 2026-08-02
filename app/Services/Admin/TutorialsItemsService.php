<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TutorialsItemsService
{
    protected string $path = 'public/tutorials-items.json';

    protected string $legacyPath = 'admin/tutorials-items.json';

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getItems(): array
    {
        $path = $this->resolveReadablePath();

        if ($path === null) {
            return [];
        }

        $contents = Storage::disk('local')->get($path);
        $items = json_decode((string) $contents, true);

        if (! is_array($items)) {
            return [];
        }

        return array_values(array_map(function (array $item): array {
            $item['id'] = (string) ($item['id'] ?? Str::slug((string) ($item['label'] ?? '')));
            $item['sortOrder'] = (int) ($item['sortOrder'] ?? 0);

            return $item;
        }, $items));
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    public function saveItems(array $items): void
    {
        $normalized = array_values(array_map(function (array $item): array {
            $item['id'] = (string) ($item['id'] ?? Str::slug((string) ($item['label'] ?? '')));
            $item['sortOrder'] = (int) ($item['sortOrder'] ?? 0);

            return $item;
        }, $items));

        usort($normalized, function (array $left, array $right): int {
            return ($left['sortOrder'] ?? 0) <=> ($right['sortOrder'] ?? 0);
        });

        Storage::disk('local')->put(
            $this->path,
            json_encode($normalized, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    protected function resolveReadablePath(): ?string
    {
        if (Storage::disk('local')->exists($this->path)) {
            return $this->path;
        }

        if (Storage::disk('local')->exists($this->legacyPath)) {
            return $this->legacyPath;
        }

        return null;
    }
}
