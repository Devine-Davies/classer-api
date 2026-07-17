<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;

/**
 * Admin Controller
 */
class AdminController extends Controller
{
    /**
     * Get the list of log files.
     *
     * @return JsonResponse
     */
    public function logs(?string $filename = null)
    {
        $logDirectory = storage_path('logs');

        if ($filename) {
            $safeFilename = basename($filename);

            if (! str_ends_with($safeFilename, '.log')) {
                return response()->json([
                    'message' => 'Invalid log file.',
                ], 422);
            }

            $filePath = $logDirectory.'/'.$safeFilename;

            if (! File::exists($filePath)) {
                return response()->json([
                    'message' => 'Log file not found.',
                ], 404);
            }

            return response()->json([
                'filename' => $safeFilename,
                'lines' => $this->tailFile($filePath, 500),
            ]);
        }

        $files = File::files($logDirectory);
        $logFiles = [];

        foreach ($files as $file) {
            if ($file->getExtension() !== 'log') {
                continue;
            }

            $logFiles[] = [
                'name' => $file->getFilename(),
                'size' => $file->getSize(),
                'last_modified' => date('Y-m-d H:i:s', $file->getMTime()),
            ];
        }

        return response()->json($logFiles);
    }

    /**
     * Tail a log file and return the last N lines.
     */
    protected function tailFile(string $path, int $lines = 500): array
    {
        $file = new \SplFileObject($path, 'r');
        $file->seek(PHP_INT_MAX);

        $lastLine = $file->key();
        $startLine = max(0, $lastLine - $lines);

        $output = [];

        $file->seek($startLine);

        while (! $file->eof()) {
            $line = rtrim($file->fgets(), "\r\n");

            if ($line !== '') {
                $output[] = $line;
            }
        }

        return $output;
    }
}
