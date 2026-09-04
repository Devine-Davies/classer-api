<?php

namespace Tests\Unit\Services;

use App\Logging\AppLogger;
use App\Models\CloudEntity;
use App\Models\CloudShare;
use App\Services\CloudShareCleanupService;
use App\Services\CloudStorageService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CloudShareCleanupServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('classer.userStorage.disk', 'user-storage');
        config()->set('classer.cloudShare.directory_key', 'cloud-share');
        config()->set('filesystems.disks.user-storage.directories.cloud-share', 'classermedia.com/cloud-share');
    }

    public function test_it_resolves_current_multi_segment_cloud_share_directory(): void
    {
        $service = $this->service();
        $share = $this->shareWithKey('classermedia.com/cloud-share/current-share/video.mp4');

        $this->assertSame(
            'classermedia.com/cloud-share/current-share',
            $service->resolveDirectory($share)
        );
        $this->assertSame('user-storage', $service->resolveDiskForPath('classermedia.com/cloud-share/current-share'));
    }

    public function test_it_rejects_unrecognized_cloud_share_keys(): void
    {
        $service = $this->service();
        $share = $this->shareWithKey('cloud-share/old-share/video.mp4');

        $this->assertNull($service->resolveDirectory($share));
        $this->assertNull($service->resolveDiskForPath('cloud-share/old-share/video.mp4'));
    }

    public function test_it_deletes_current_directories_from_user_storage(): void
    {
        Storage::fake('user-storage');

        Storage::disk('user-storage')->put('classermedia.com/cloud-share/current-share/video.mp4', 'current');

        $service = $this->service();

        $this->assertTrue($service->deleteDirectory('classermedia.com/cloud-share/current-share'));
        Storage::disk('user-storage')->assertMissing('classermedia.com/cloud-share/current-share/video.mp4');
    }

    protected function shareWithKey(string $key): CloudShare
    {
        $share = new CloudShare;
        $share->setRelation('cloudEntities', collect([
            new CloudEntity(['key' => $key]),
        ]));

        return $share;
    }

    protected function service(): CloudShareCleanupService
    {
        return new CloudShareCleanupService(
            new AppLogger,
            new CloudStorageService(new AppLogger)
        );
    }
}
