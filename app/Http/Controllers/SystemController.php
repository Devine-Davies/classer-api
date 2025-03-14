<?php

namespace App\Http\Controllers;

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

    public function loadFromResource($path)
    {
        return json_decode(
            file_get_contents(
                resource_path($path)
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
    public function latestReleases(Request $request)
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

    /**
     * Latest Releases Path
     */
    public function latestReleasesPath($platform, $architecture)
    {
        $versionKey = $platform . '-' . $architecture;
        $releases = $this->releases;

        if (!isset($releases[$versionKey])) {
            return false;
        }
    
        $latestVersion = null;
    
        foreach ($releases[$versionKey] as $version => $release) {
            if ($release === '@latest') {
                $latestVersion = $version;
                break;
            }
        }
    
        if ($latestVersion === null) {
            return false;
        }    
    
        return public_path('downloads' . DIRECTORY_SEPARATOR . $versionKey . DIRECTORY_SEPARATOR . $latestVersion . '.zip');
    }
}

