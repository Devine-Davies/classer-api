<?php

namespace Tests\Unit\Services;

use App\Logging\AppLogger;
use App\Services\CloudStorageService;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

class CloudStorageServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('classer.userStorage.disk', 'user-storage');
        Storage::fake('user-storage');
    }

    public function test_it_deletes_objects_and_directories_from_user_storage(): void
    {
        $storage = new CloudStorageService(new AppLogger);
        Storage::disk('user-storage')->put('backups/one/file.mp4', 'video');
        Storage::disk('user-storage')->put('backups/two/file.mp4', 'video');

        $this->assertTrue($storage->deleteObject('backups/one/file.mp4'));
        $this->assertTrue($storage->deleteDirectory('backups/two'));
        Storage::disk('user-storage')->assertMissing('backups/one/file.mp4');
        Storage::disk('user-storage')->assertMissing('backups/two/file.mp4');
    }

    public function test_it_rejects_an_empty_object_key(): void
    {
        $storage = new CloudStorageService(new AppLogger);

        $this->expectException(InvalidArgumentException::class);
        $storage->createDownloadUrl('');
    }
}
