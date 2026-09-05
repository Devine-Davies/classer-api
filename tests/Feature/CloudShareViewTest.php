<?php

namespace Tests\Feature;

use App\Enums\CloudEntityRole;
use App\Enums\CloudShareStatus;
use App\Http\Resources\CloudShareResource;
use App\Models\CloudShare;
use App\Models\User;
use App\Services\CloudStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class CloudShareViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_url_is_derived_from_the_share_uid_and_not_persisted(): void
    {
        $share = $this->createShare(CloudShareStatus::ACTIVE, now()->addHour());

        $resource = CloudShareResource::make($share)->resolve();

        $this->assertSame(route('cloud-share.show', ['uid' => $share->uid]), $resource['publicUrl']);
        $this->assertFalse(Schema::hasColumn('cloud_share', 'public_url'));
        $this->assertFalse(Schema::hasColumn('cloud_entities', 'public_url'));
        $this->assertFalse(Schema::hasColumn('cloud_entities', 'download_url'));
    }

    public function test_active_share_generates_fresh_download_urls_when_rendered(): void
    {
        $share = $this->createShare(CloudShareStatus::ACTIVE, now()->addHour());
        $share->cloudEntities()->createMany([
            $this->entity(CloudEntityRole::VIDEO, 'cloud-share/'.$share->uid.'/video.mp4'),
            $this->entity(CloudEntityRole::THUMBNAIL, 'cloud-share/'.$share->uid.'/thumbnail.jpg'),
        ]);

        $storage = Mockery::mock(CloudStorageService::class);
        $storage->shouldReceive('createDownloadUrl')
            ->twice()
            ->andReturnUsing(fn (string $key): string => 'https://storage.test/'.$key);
        $this->app->instance(CloudStorageService::class, $storage);

        $response = $this->get(route('cloud-share.show', ['uid' => $share->uid]));

        $response->assertOk();
        $response->assertViewIs('share-moment');
        $response->assertViewHas('videoSrc', 'https://storage.test/cloud-share/'.$share->uid.'/video.mp4');
        $response->assertViewHas('thumbnailSrc', 'https://storage.test/cloud-share/'.$share->uid.'/thumbnail.jpg');
    }

    public function test_inactive_or_expired_share_is_not_rendered_or_signed(): void
    {
        $inactive = $this->createShare(CloudShareStatus::VALIDATING, now()->addHour());
        $expired = $this->createShare(CloudShareStatus::ACTIVE, now()->subMinute());

        $storage = Mockery::mock(CloudStorageService::class);
        $storage->shouldNotReceive('createDownloadUrl');
        $this->app->instance(CloudStorageService::class, $storage);

        $this->get(route('cloud-share.show', ['uid' => $inactive->uid]))->assertNotFound();
        $this->get(route('cloud-share.show', ['uid' => $expired->uid]))->assertNotFound();
    }

    private function createShare(CloudShareStatus $status, $expiresAt): CloudShare
    {
        $user = User::factory()->create();

        return CloudShare::create([
            'uid' => (string) Str::uuid(),
            'user_id' => $user->uid,
            'resource_id' => (string) Str::uuid(),
            'status' => $status,
            'expires_at' => $expiresAt,
        ]);
    }

    private function entity(CloudEntityRole $role, string $key): array
    {
        return [
            'uid' => (string) Str::uuid(),
            'key' => $key,
            'object_role' => $role,
            'original_name' => basename($key),
            'mime_type' => $role === CloudEntityRole::VIDEO ? 'video/mp4' : 'image/jpeg',
        ];
    }
}
