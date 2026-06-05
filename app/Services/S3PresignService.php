<?php

namespace App\Services;

use App\Logging\AppLogger;
use Aws\S3\S3Client;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

/**
 * S3PresignService
 */
class S3PresignService
{
    protected S3Client $client;

    protected string $bucket;

    /**
     * Create S3 client and hydrate bucket configuration.
     *
     * @return void
     */
    public function __construct(protected AppLogger $logger)
    {
        $this->logger->setContext('S3PresignService');
        $this->bucket = config('filesystems.disks.s3.bucket');
        $this->client = new S3Client([
            'region' => config('filesystems.disks.s3.region'),
            'version' => 'latest',
            'credentials' => [
                'key' => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret'),
            ],
        ]);

        $this->logger->info('Initialized S3 presign client', [
            'bucket' => $this->bucket,
            'region' => config('filesystems.disks.s3.region'),
        ]);
    }

    /**
     * Generate presigned upload URLs for a given share UID and entity payloads.
     *
     * @param  string  $shareUid  The UUID of the CloudShare record
     * @param  array[]  $entities  Each item must include:
     *                             - uid: unique client-side identifier
     *                             - sourceFile: original filename (for extension)
     *                             - contentType: MIME type
     *                             - size: file size in bytes
     * @return array[] Each item ready for createMany():
     *                 [
     *                 'uid'          => (string),
     *                 'source_file'  => (string),
     *                 'content_type' => (string),
     *                 'size'         => (int),
     *                 'key'          => (string),
     *                 'upload_url'   => (string),
     *                 ]
     *
     * @throws \Throwable
     */
    public function generateUrlsForShare(string $shareUid, array $entities): array
    {
        if (empty($entities)) {
            $this->logger->warning('No entities provided for presign generation', [
                'share_uid' => $shareUid,
            ]);
        }

        $cloudShareDir = config('classer.cloudShare.directory', 'cloud-share');
        $putObjectTimeout = config('classer.cloudShare.putObjectTimeout', '+1 hour');
        $getObjectTimeout = config('classer.cloudShare.getObjectTimeout', '+4 hours');
        $results = collect($entities)
            ->map(function (array $entity) use ($shareUid, $cloudShareDir, $putObjectTimeout, $getObjectTimeout) {
                // Preserve client UID and original filename info
                $uid = $entity['uid'];
                $sourceFile = $entity['sourceFile'];
                $contentType = $entity['contentType'];

                // It's important to note that this is the size that an external client has given us
                // Therefore a verification step is needed later to ensure the file was uploaded correctly
                $size = $entity['size'];
                $expiresAt = CarbonImmutable::parse(
                    now()->addHours(4)->toIso8601String()
                )->utc(); // Eloquent formats to 'Y-m-d H:i:s'

                // Build S3 key: <baseDir>/<shareUid>/<randomFilename>.<ext>
                $extension = pathinfo($sourceFile, PATHINFO_EXTENSION);
                $filename = Str::uuid().($extension ? ".{$extension}" : '');
                $key = "{$cloudShareDir}/{$shareUid}/{$filename}";

                // Generate presigned URLs for upload and public access
                $uploadUrl = $this->generateS3Url('PutObject', $key, $putObjectTimeout);
                $publicUrl = $this->generateS3Url('GetObject', $key, $getObjectTimeout);

                return [
                    'uid' => $uid,
                    'type' => $contentType,
                    'size' => $size,
                    'key' => $key,
                    'expires_at' => CarbonImmutable::parse($expiresAt)->toDateTimeString(),
                    'upload_url' => $uploadUrl,
                    'public_url' => $publicUrl,
                ];
            })
            ->values()
            ->all();

        $this->logger->info('Generated presigned URLs for cloud share', [
            'share_uid' => $shareUid,
            'entity_count' => count($results),
        ]);

        return $results;
    }

    /**
     * Generate a presigned URL for a specific S3 operation.
     *
     * @param  string  $httpMethod  S3 command name such as PutObject/GetObject.
     * @param  string  $key  Object key path.
     * @param  string  $expires  Relative expiration string.
     * @return string Presigned URL.
     */
    public function generateS3Url(string $httpMethod, string $key, string $expires = '+4 hours'): string
    {
        $command = $this->client->getCommand($httpMethod, [
            'Bucket' => $this->bucket,
            'Key' => $key,
        ]);

        return (string) $this->client->createPresignedRequest(
            $command,
            $expires
        )->getUri();
    }

    /**
     * Confirm the upload by checking the file sizes and storing the metadata.
     *
     * @param  string  $key  Object key path.
     * @return object{key:string, e_tag:string|null, size:int|null} Object metadata snapshot.
     *
     * @throws \Throwable
     */
    public function getObjectMeta(string $key): object
    {
        try {
            $result = $this->client->headObject([
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

        return (object) [
            'key' => $key,
            'e_tag' => trim($result['ETag'], '"') ?? null,
            'size' => $result['ContentLength'] ?? null,
        ];
    }
}

// $command = $client->getCommand('GetObject', [
//     'Bucket' => $bucket,
//     'Key' => $entity->key,
// ]);

// $options = [
//     'ResponseContentDisposition' => 'inline',
//     'ResponseContentType' => $entity->type,
// ];

// $entity->expires_at = $expires;
// $entity->public_url = (string) $client->createPresignedRequest(
//     // $command,
//     // $expires,
//     $options
// )->getUri();
