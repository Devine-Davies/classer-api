<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadsController extends Controller
{
    public function download(string $file_name): BinaryFileResponse
    {
        // Strip any directory components to prevent path traversal (e.g. "../../etc/passwd").
        $safeName = basename($file_name);

        $file_path = public_path('downloads/'.$safeName);

        // Ensure the resolved path is still inside the downloads directory.
        $downloadsRoot = realpath(public_path('downloads'));
        $resolvedPath = realpath($file_path);

        if ($downloadsRoot === false
            || $resolvedPath === false
            || ! str_starts_with($resolvedPath, $downloadsRoot.DIRECTORY_SEPARATOR)) {
            abort(404);
        }

        return response()->download($resolvedPath);
    }
}
