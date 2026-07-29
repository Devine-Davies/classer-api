<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Latest Version
 * http://localhost/api/versions?platform=windows&architecture=x64
 * http://localhost/api/versions?platform=windows&architecture=x64&version=1.0.2
 * http://localhost/api/versions?platform=windows&architecture=x86&version=1.0.2
 * http://localhost/api/versions?platform=macOS&architecture=x64&version=1.0.2
 * http://localhost/api/versions?platform=macOS&architecture=arm64&version=1.0.2
 */
class SystemController extends Controller
{
    private array $releases = [];

    public function __construct()
    {
        $releasesPath = resource_path('releases.json');

        if (is_file($releasesPath)) {
            $decoded = json_decode(
                (string) file_get_contents($releasesPath),
                true
            );

            $this->releases = is_array($decoded) ? $decoded : [];
        }
    }

    public function loadFromResource(string $path): array
    {
        $resolved = resource_path($path);

        if (! is_file($resolved)) {
            return [];
        }

        $decoded = json_decode(
            (string) file_get_contents($resolved),
            true
        );

        return is_array($decoded) ? $decoded : [];
    }

    public function versions(Request $request)
    {
        $version = $request->header('x-app-version');
        $platform = $request->header('x-app-platform');
        $architecture = $request->header('x-app-architecture');
        $versionKey = $platform.'-'.$architecture;
        $releases = $this->releases;

        if (! isset($releases[$versionKey])) {
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

        $versionKey = $platform.'-'.$architecture;
        $releases = $this->releases;

        if (! isset($releases[$versionKey])) {
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

        $downloadPath = public_path('downloads'.DIRECTORY_SEPARATOR.$versionKey.DIRECTORY_SEPARATOR.$latestVersion.'.zip');

        if (! file_exists($downloadPath)) {
            return response()->json('@error');
        }

        return response()->download($downloadPath);
    }

    /**
     * Latest Releases Path
     */
    public function latestReleasesPath($platform, $architecture)
    {
        $versionKey = $platform.'-'.$architecture;
        $releases = $this->releases;

        if (! isset($releases[$versionKey])) {
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

        return public_path('downloads'.DIRECTORY_SEPARATOR.$versionKey.DIRECTORY_SEPARATOR.$latestVersion.'.zip');
    }
}
