<?php

namespace App\Services;

use App\Logging\AppLogger;
use Aws\S3\S3Client;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class S3PresignService
{
    protected ?S3Client $client = null;

    protected string $bucket;

    protected string $cloudShareDir;

    public function __construct(protected AppLogger $logger)
    {
        $this->logger->setContext('S3PresignService');

        $this->bucket = (string) config('filesystems.disks.s3.bucket');
        $this->cloudShareDir = (string) config('classer.cloudShare.directory', 'cloud-share');
    }

    protected function getClient(): S3Client
    {
        if ($this->client instanceof S3Client) {
            return $this->client;
        }

        $region = (string) config('filesystems.disks.s3.region');
        $key = (string) config('filesystems.disks.s3.key');
        $secret = (string) config('filesystems.disks.s3.secret');

        if ($this->bucket === '' || $region === '' || $key === '' || $secret === '') {
            throw new RuntimeException('S3 configuration is incomplete.');
        }

        // $this->client = new S3Client([
        //     'region' => $region,
        //     'version' => 'latest',
        //     'credentials' => [
        //         'key' => $key,
        //         'secret' => $secret,
        //     ],
        // ]);

        $this->client = new S3Client([
            'region' => config('filesystems.disks.s3.region'),
            'version' => 'latest',
            'endpoint' => config('filesystems.disks.s3.endpoint'),
            'use_path_style_endpoint' => config('filesystems.disks.s3.use_path_style_endpoint'),
            'credentials' => [
                'key' => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret'),
            ],
        ]);

        $this->logger->info('Initialized S3 presign client', [
            'bucket' => $this->bucket,
            'region' => $region,
        ]);

        return $this->client;
    }

    /**
     * Generate presigned URLs for a set of entities associated with a cloud share.
     *
     * @param  string  $shareUid  The UID of the cloud share for which to generate URLs.
     * @param  array  $entities  An array of entities, each containing 'uid', 'sourceFile', 'contentType', and 'size'.
     * @return array An array of entities with added 'key', 'expires_at', 'upload_url', and 'public_url' fields.
     *
     * @throws InvalidArgumentException if required parameters are missing or invalid.
     */
    public function generateUrlsForShare(string $shareUid, array $entities): array
    {
        if (trim($shareUid) === '') {
            throw new InvalidArgumentException('Share UID is required for presign generation.');
        }

        if (empty($entities)) {
            throw new InvalidArgumentException('Cannot generate presigned URLs without entities.');
        }

        $putObjectTimeout = (string) config('classer.cloudShare.putObjectTimeout', '+1 minute');
        $getObjectTimeout = (string) config('classer.cloudShare.getObjectTimeout', '+2 minutes');

        $results = collect($entities)
            ->map(fn (array $entity): array => $this->buildPresignedEntityPayload(
                $shareUid,
                $entity,
                $putObjectTimeout,
                $getObjectTimeout
            ))
            ->values()
            ->all();

        $this->logger->info('Generated presigned URLs for cloud share', [
            'share_uid' => $shareUid,
            'entity_count' => count($results),
        ]);

        return $results;
    }

    /**
     * Build the payload for a single entity, including presigned URLs for upload and public access.
     *
     * @param  string  $shareUid  The UID of the cloud share.
     * @param  array  $entity  An array containing 'uid', 'sourceFile', 'contentType', and 'size' for the entity.
     * @param  string  $putObjectTimeout  The expiration time for the upload URL.
     * @param  string  $getObjectTimeout  The expiration time for the public URL.
     * @return array An array containing the original entity data along with 'key', 'expires_at', 'upload_url', and 'public_url'.
     *
     * @throws InvalidArgumentException if required entity fields are missing or invalid.
     */
    protected function buildPresignedEntityPayload(
        string $shareUid,
        array $entity,
        string $putObjectTimeout,
        string $getObjectTimeout
    ): array {
        $uid = (string) ($entity['uid'] ?? '');
        $sourceFile = (string) ($entity['sourceFile'] ?? '');
        $contentType = (string) ($entity['contentType'] ?? '');
        $size = (int) ($entity['size'] ?? 0);

        if ($uid === '') {
            throw new InvalidArgumentException('Entity UID is required for presign generation.');
        }

        if ($sourceFile === '') {
            throw new InvalidArgumentException(sprintf(
                'Source file is required for entity %s.',
                $uid
            ));
        }

        if ($contentType === '') {
            throw new InvalidArgumentException(sprintf(
                'Content type is required for entity %s.',
                $uid
            ));
        }

        if ($size <= 0) {
            throw new InvalidArgumentException(sprintf(
                'File size must be greater than zero for entity %s.',
                $uid
            ));
        }

        $extension = pathinfo($sourceFile, PATHINFO_EXTENSION);
        $filename = (string) Str::uuid().($extension ? ".{$extension}" : '');
        $key = "{$this->cloudShareDir}/{$shareUid}/{$filename}";

        $expiresAt = CarbonImmutable::now()
            ->addHours(4)
            ->utc()
            ->toDateTimeString();

        return [
            'uid' => $uid,
            'source_file' => $sourceFile,
            'type' => $contentType,
            'size' => $size,
            'key' => $key,
            'expires_at' => $expiresAt,
            'upload_url' => $this->generateS3Url('PutObject', $key, $putObjectTimeout, [
                'ContentType' => $contentType,
            ]),
            'public_url' => $this->generateS3Url('GetObject', $key, $getObjectTimeout),
        ];
    }

    /**
     * Generate a presigned URL for a given S3 operation and key.
     *
     * @param  string  $operation  The S3 operation (e.g., 'PutObject', 'GetObject').
     * @param  string  $key  The S3 object key for which to generate the presigned URL.
     * @param  string  $expires  The expiration time for the presigned URL (e.g., '+1 hour').
     * @param  array  $options  Additional options to pass to the S3 command.
     * @return string The generated presigned URL.
     *
     * @throws InvalidArgumentException if required parameters are missing or invalid.
     * @throws \Throwable if the S3 client fails to generate the presigned URL.
     */
    public function generateS3Url(
        string $operation,
        string $key,
        string $expires = '+4 hours',
        array $options = []
    ): string {
        if ($key === '') {
            throw new InvalidArgumentException('S3 key is required for presign generation.');
        }

        $command = $this->getClient()->getCommand($operation, array_merge([
            'Bucket' => $this->bucket,
            'Key' => $key,
        ], $options));

        return (string) $this->getClient()
            ->createPresignedRequest($command, $expires)
            ->getUri();
    }

    /**
     * Fetch metadata for an S3 object key.
     *
     * @param  string  $key  The S3 object key.
     * @return object Object containing key, e_tag and size.
     */
    public function getObjectMeta(string $key): object
    {
        if ($key === '') {
            throw new InvalidArgumentException('S3 key is required to fetch object metadata.');
        }

        try {
            $result = $this->getClient()->headObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
            ]);
        } catch (\Throwable $exception) {
            $this->logger->error('Failed to fetch S3 object metadata', [
                'bucket' => $this->bucket,
                'key' => $key,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        // ETag is not always present (e.g., for multipart uploads), so we trim quotes if it exists, otherwise return null.
        $eTag = isset($result['ETag'])
            ? trim((string) $result['ETag'], '"')
            : null;

        return (object) [
            'key' => $key,
            'e_tag' => $eTag,
            'size' => isset($result['ContentLength'])
                ? (int) $result['ContentLength']
                : null,
        ];
    }
}
