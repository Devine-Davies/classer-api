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
    private $releases;

    public function __construct()
    {
        $this->releases = $this->loadReleases();
    }

    private function loadReleases()
    {
        return json_decode(
            file_get_contents(
                resource_path('releases.json')
            ),
            true
        );
    }

    public function versions(Request $request)
    {
        $version = $request->header('x-app-version');
        $platform = $request->header('x-app-platform');
        $architecture = $request->header('x-app-architecture');
        $versionKey = $platform . '-' . $architecture;
        $releases = $this->releases;

        if (!isset($releases[$versionKey])) {
            return response()->json('@error');
        }

        return response()->json(
            $releases[$versionKey][$version] ?? '@error'
        );
    }

    /**
     * http://localhost/api/releases/download/latest?platform=windows&architecture=x64
     */
    public function downloadLatestReleases(Request $request)
    {
        $platform = $request->platform;
        $architecture = $request->architecture;

        $versionKey = $platform . '-' . $architecture;
        $releases = $this->releases;

        if (!isset($releases[$versionKey])) {
            return response()->json('@error');
        }

        $latestVersion = null;

        foreach ($releases[$versionKey] as $version => $release) {
            if ($release === '@latest') {
                $latestVersion = $version;
                break;
            }
        }

        if ($latestVersion === null) {
            return response()->json('@error');
        }

        $downloadPath = public_path('downloads' . DIRECTORY_SEPARATOR . $versionKey . DIRECTORY_SEPARATOR . $latestVersion . '.zip');

        if (!file_exists($downloadPath)) {
            return response()->json('@error');
        }

        return response()->download($downloadPath);
    }
}
