<?php

namespace App\Services\Admin;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class LogsService
{
    /**
     * Build the list of available .log files from storage/logs.
     */
    public function availableFiles(): Collection
    {
        $directory = storage_path('logs');

        if (! File::exists($directory)) {
            return collect();
        }

        return collect(File::files($directory))
            ->filter(fn ($file): bool => $file->getExtension() === 'log')
            ->map(fn ($file): array => [
                'filename' => $file->getFilename(),
                'size' => $file->getSize(),
                'last_modified' => $file->getMTime(),
            ])
            ->sortByDesc('last_modified')
            ->values();
    }

    /**
     * Resolve the active log filename from query params or fallback to first file.
     */
    public function resolveActiveFilename(Request $request, Collection $files): ?string
    {
        $requestedFile = basename(trim((string) $request->query('file', '')));

        if ($requestedFile !== '' && $this->isValidLogFilename($requestedFile)) {
            $exists = $files->contains(
                fn (array $item): bool => ($item['filename'] ?? '') === $requestedFile
            );

            if ($exists) {
                return $requestedFile;
            }
        }

        return $files->first()['filename'] ?? null;
    }

    /**
     * Paginate parsed log rows for a specific file.
     */
    public function paginateRows(Request $request, string $filename): LengthAwarePaginator
    {
        $safeFilename = basename($filename);

        if (! $this->isValidLogFilename($safeFilename)) {
            return $this->emptyPaginator($request);
        }

        $filePath = storage_path('logs/'.$safeFilename);

        if (! File::exists($filePath)) {
            return $this->emptyPaginator($request);
        }

        $perPage = max(10, min((int) $request->query('limit', 50), 200));
        $currentPage = max((int) $request->query('page', 1), 1);
        $search = Str::lower(trim((string) $request->query('q', '')));

        $rawLines = @file($filePath, FILE_IGNORE_NEW_LINES);
        $rawLines = is_array($rawLines) ? $rawLines : [];

        $rows = collect($rawLines)
            ->filter(fn ($line): bool => trim((string) $line) !== '')
            ->reverse()
            ->values()
            ->map(function (string $line): array {
                $parsed = $this->parseLine($line);

                $parsed['search'] = Str::lower(implode(' ', [
                    $parsed['type'],
                    $parsed['timestamp'],
                    $parsed['context'],
                    $parsed['message'],
                    $parsed['data'],
                ]));

                return $parsed;
            });

        if ($search !== '') {
            $rows = $rows->filter(
                fn (array $row): bool => Str::contains($row['search'], $search)
            )->values();
        }

        $total = $rows->count();
        $items = $rows->forPage($currentPage, $perPage)->values()->all();

        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => url()->current(),
                'query' => $request->query(),
            ]
        );
    }

    /**
     * Truncate a log file.
     */
    public function clearFile(string $filename): bool
    {
        $safeFilename = basename(trim($filename));

        if (! $this->isValidLogFilename($safeFilename)) {
            return false;
        }

        $filePath = storage_path('logs/'.$safeFilename);

        if (! File::exists($filePath)) {
            return false;
        }

        return File::put($filePath, '') !== false;
    }

    private function emptyPaginator(Request $request): LengthAwarePaginator
    {
        $perPage = max(10, min((int) $request->query('limit', 50), 200));
        $currentPage = max((int) $request->query('page', 1), 1);

        return new LengthAwarePaginator(
            [],
            0,
            $perPage,
            $currentPage,
            [
                'path' => url()->current(),
                'query' => $request->query(),
            ]
        );
    }

    private function isValidLogFilename(string $filename): bool
    {
        return preg_match('/^[A-Za-z0-9_\-.]+\.log$/', $filename) === 1;
    }

    /**
     * Parse a raw log line into table-friendly fields.
     */
    private function parseLine(string $line): array
    {
        $value = trim($line);

        preg_match('/^\[([^\]]+)]\s+([^\.]+)\.([A-Z]+):\s+(.*)$/', $value, $laravelMatch);

        if ($laravelMatch !== []) {
            return [
                'type' => $laravelMatch[3],
                'timestamp' => $laravelMatch[1],
                'context' => $laravelMatch[2],
                'message' => $laravelMatch[4],
                'data' => $value,
            ];
        }

        if (Str::startsWith($value, '#')) {
            return [
                'type' => 'TRACE',
                'timestamp' => '-',
                'context' => 'stack',
                'message' => $value,
                'data' => $value,
            ];
        }

        if (Str::contains($value, '[previous exception]')) {
            return [
                'type' => 'ERROR',
                'timestamp' => '-',
                'context' => 'exception',
                'message' => $value,
                'data' => $value,
            ];
        }

        return [
            'type' => 'LOG',
            'timestamp' => '-',
            'context' => 'raw',
            'message' => $value,
            'data' => $value,
        ];
    }
}
