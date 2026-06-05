<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

/**
 * Admin Controller
 */
class AdminController extends Controller
{
    /**
     * Get logs
     * @param \Illuminate\Http\Request $request
     * @param mixed $filename
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function logs(Request $request, $filename = 'laravel.log')
    {
        $path = storage_path("logs/{$filename}");

        if (!File::exists($path)) {
            return response()->json(['message' => "Log file '{$filename}' not found."], 404);
        }

        $lines = explode("\n", File::get($path));
        $tail = array_slice($lines, -200);

        return response()->json($tail);
    }
}
