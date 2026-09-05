<?php

namespace App\Services;

use App\Logging\AppLogger;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use RuntimeException;

/**
 * Service for interacting with cloud storage using S3.
 *
 * @description This service provides methods for generating presigned URLs,
 *              retrieving object metadata, and managing objects and directories
 *              in cloud storage.
 */
class CloudStorageService
{
    /**
     * The S3 client instance for interacting with cloud storage.
     */
    protected ?S3Client $client = null;

    /**
     * The filesystem disk used for cloud storage.
     */
    protected string $disk;

    /**
     * The name of the cloud storage bucket.
     */
    protected string $bucket;

    /**
     * The filesystem disk used for cloud storage.
     */
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

    /**
     * Create a presigned upload URL for a cloud storage object.
     *
     * @description This method generates a presigned URL for uploading an object to cloud storage,
     *              ensuring that the content type is specified and the URL expires after a given duration.
     *
     * @param string $key
     * @param string $contentType
     * @param string $expires
     * @return string
     */
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

    /**
     * Create a presigned download URL for a cloud storage object.
     *
     * @description This method generates a presigned URL for downloading an object from cloud storage,
     *              ensuring that the URL expires after a given duration.
     *
     * @param string $key
     * @param string $expires
     * @return string
     */
    public function createDownloadUrl(string $key, string $expires = '+2 minutes'): string
    {
        return $this->createPresignedUrl('GetObject', $key, $expires);
    }

    /**
     * Retrieve metadata for a cloud storage object.
     *
     * @description This method fetches the metadata of a specified cloud storage object,
     *              including its ETag and size.
     *
     * @param string $key
     * @return object
     */
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

    /**
     * Delete a cloud storage object.
     *
     * @description This method deletes the specified object from cloud storage.
     *
     * @param string $key
     * @return bool
     */
    public function deleteObject(string $key): bool
    {
        $this->assertKey($key);

        return Storage::disk($this->disk)->delete($key);
    }

    /**
     * Delete a cloud storage directory.
     *
     * @description This method deletes the specified directory and its contents from cloud storage.
     *
     * @param string $directory
     * @return bool
     */
    public function deleteDirectory(string $directory): bool
    {
        $this->assertKey($directory);

        return Storage::disk($this->disk)->deleteDirectory($directory);
    }

    /**
     * Create a presigned URL for a cloud storage object.
     *
     * @description This method generates a presigned URL for a specified operation on a cloud storage object,
     *              ensuring that the URL expires after a given duration.
     *
     * @param string $operation
     * @param string $key
     * @param string $expires
     * @param array $options
     * @return string
     */
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

    /**
     * Get the S3 client instance.
     *
     * @description This method retrieves the configured S3 client for interacting with cloud storage.
     *
     * @return S3Client
     */
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

    /**
     * Assert that a cloud storage object key is valid.
     *
     * @description This method checks if the provided object key is non-empty and throws an exception if it is invalid.
     *
     * @param string $key
     * @return void
     */
    protected function assertKey(string $key): void
    {
        if (trim($key) === '') {
            throw new InvalidArgumentException('Cloud storage object key is required.');
        }
    }
}
