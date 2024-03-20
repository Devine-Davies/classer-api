<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Latest Version
 * http://localhost/api/versions?platform=xxx&architecture=xxx
 * http://localhost/api/versions?platform=windows&architecture=x64&version=1.0.2
 * http://localhost/api/versions?platform=windows&architecture=x86&version=1.0.2
 * http://localhost/api/versions?platform=macOS&architecture=x64&version=1.0.2
 * http://localhost/api/versions?platform=macOS&architecture=arm64&version=1.0.2
 */
class SystemController extends Controller
{
    const DEV_VERSION_STATUS = '@update';
    const VERSIONS = [
        'windows-x64' => [
            '0.0.0' => self::DEV_VERSION_STATUS,
            '1.0.0' => '@deprecated',
            '1.0.1' => '@update',
            '1.0.2' => '@latest',
        ],
        'windows-x86' => [
            '0.0.0' => self::DEV_VERSION_STATUS,
            '1.0.0' => '@deprecated',
            '1.0.1' => '@update',
            '1.0.2' => '@latest',
        ],
        'darwin-x64' => [
            '0.0.0' => self::DEV_VERSION_STATUS,
            '1.0.0' => '@deprecated',
            '1.0.1' => '@update',
            '1.0.2' => '@latest',
        ],
        'darwin-arm64' => [
            '0.0.0' => self::DEV_VERSION_STATUS,
            '1.0.0' => '@deprecated',
            '1.0.1' => '@update',
            '1.0.2' => '@latest',
        ],
    ];

    public function versions(Request $request)
    {
        $version = $request->header('x-app-version');
        $platform = $request->header('x-app-platform');
        $architecture = $request->header('x-app-architecture');
        $versionKey = $platform . '-' . $architecture;
        if (!isset(self::VERSIONS[$versionKey])) {
            return response()->json([
                'error' => 'Platform and architecture not found',
            ], 404);
        }

        return response()->json(self::VERSIONS[$versionKey][$version] ?? '@not-found');
    }
}
