<?php

namespace App\Services;

use App\Logging\AppLogger;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use RuntimeException;

class CloudStorageService
{
    protected ?S3Client $client = null;

    protected string $disk;

    protected string $bucket;

    public function __construct(protected AppLogger $logger)
    {
        $this->logger->setContext('CloudStorageService');
        $this->disk = (string) config('classer.userStorage.disk', 'user-storage');
        $configuredDisks = (array) config('filesystems.disks', []);

        if (! array_key_exists($this->disk, $configuredDisks)) {
            throw new RuntimeException("Cloud storage filesystem disk is not configured: {$this->disk}");
        }

        $this->bucket = (string) config("filesystems.disks.{$this->disk}.bucket", '');
    }

    public function createUploadUrl(
        string $key,
        string $contentType,
        string $expires = '+1 minute'
    ): string {
        if ($contentType === '') {
            throw new InvalidArgumentException('Content type is required for upload URL generation.');
        }

        return $this->createPresignedUrl('PutObject', $key, $expires, [
            'ContentType' => $contentType,
        ]);
    }

    public function createDownloadUrl(string $key, string $expires = '+2 minutes'): string
    {
        return $this->createPresignedUrl('GetObject', $key, $expires);
    }

    public function headObject(string $key): object
    {
        $this->assertKey($key);

        try {
            $result = $this->getClient()->headObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
            ]);
        } catch (\Throwable $exception) {
            $this->logger->error('Failed to fetch cloud object metadata', [
                'bucket' => $this->bucket,
                'key' => $key,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        return (object) [
            'key' => $key,
            'e_tag' => isset($result['ETag'])
                ? trim((string) $result['ETag'], '"')
                : null,
            'size' => isset($result['ContentLength'])
                ? (int) $result['ContentLength']
                : null,
        ];
    }

    public function deleteObject(string $key): bool
    {
        $this->assertKey($key);

        return Storage::disk($this->disk)->delete($key);
    }

    public function deleteDirectory(string $directory): bool
    {
        $this->assertKey($directory);

        return Storage::disk($this->disk)->deleteDirectory($directory);
    }

    protected function createPresignedUrl(
        string $operation,
        string $key,
        string $expires,
        array $options = []
    ): string {
        $this->assertKey($key);

        try {
            $command = $this->getClient()->getCommand($operation, array_merge([
                'Bucket' => $this->bucket,
                'Key' => $key,
            ], $options));

            return (string) $this->getClient()
                ->createPresignedRequest($command, $expires)
                ->getUri();
        } catch (\Throwable $exception) {
            $this->logger->error('Failed to generate cloud storage URL', [
                'operation' => $operation,
                'bucket' => $this->bucket,
                'key' => $key,
                'expires' => $expires,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    protected function getClient(): S3Client
    {
        if ($this->client instanceof S3Client) {
            return $this->client;
        }

        $region = (string) config("filesystems.disks.{$this->disk}.region", '');
        $key = (string) config("filesystems.disks.{$this->disk}.key", '');
        $secret = (string) config("filesystems.disks.{$this->disk}.secret", '');

        if ($this->bucket === '' || $region === '' || $key === '' || $secret === '') {
            throw new RuntimeException('Cloud storage S3 configuration is incomplete.');
        }

        $this->client = new S3Client([
            'region' => $region,
            'version' => 'latest',
            'endpoint' => config("filesystems.disks.{$this->disk}.endpoint"),
            'use_path_style_endpoint' => config("filesystems.disks.{$this->disk}.use_path_style_endpoint"),
            'credentials' => [
                'key' => $key,
                'secret' => $secret,
            ],
        ]);

        return $this->client;
    }

    protected function assertKey(string $key): void
    {
        if (trim($key) === '') {
            throw new InvalidArgumentException('Cloud storage object key is required.');
        }
    }
}
