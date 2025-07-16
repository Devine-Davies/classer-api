<?php

namespace App\Services;

use Illuminate\Support\Str;
use Aws\S3\S3Client;

/**
 * S3PresignService
 */
class S3PresignService
{
    protected S3Client $client;
    protected string $bucket;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->bucket = config('filesystems.disks.s3.bucket');
        $this->client = new S3Client([
            'region' => config('filesystems.disks.s3.region'),
            'version' => 'latest',
            'credentials' => [
                'key' => config('filesystems.disks.s3.key'),
                'secret' => config('filesystems.disks.s3.secret'),
            ],
        ]);
    }

    /**
     * Generate presigned upload URLs for a given share UID and entity payloads.
     *
     * @param  string  $shareUid        The UUID of the CloudShare record
     * @param  array[] $entities        Each item must include:
     *                                  - uid: unique client-side identifier
     *                                  - sourceFile: original filename (for extension)
     *                                  - contentType: MIME type
     *                                  - size: file size in bytes
     * @return array[]                  Each item ready for createMany():
     *                                  [
     *                                    'uid'          => (string),
     *                                    'source_file'  => (string),
     *                                    'content_type' => (string),
     *                                    'size'         => (int),
     *                                    'key'          => (string),
     *                                    'upload_url'   => (string),
     *                                  ]
     */
    public function generateUploadUrlsForShare(string $shareUid, array $entities): array
    {
        $cloudShareDir = config('classer.cloud_share_directory', 'cloud-share');
        return collect($entities)
            ->map(function (array $entity) use ($shareUid, $cloudShareDir) {
                // Preserve client UID and original filename info
                $uid         = $entity['uid'];
                $sourceFile  = $entity['sourceFile'];
                $contentType = $entity['contentType'];
                $size        = $entity['size'];

                // Build S3 key: <baseDir>/<shareUid>/<randomFilename>.<ext>
                $extension  = pathinfo($sourceFile, PATHINFO_EXTENSION);
                $filename   = Str::uuid() . ($extension ? ".{$extension}" : '');
                $key        = "{$cloudShareDir}/{$shareUid}/{$filename}";

                // Prepare the AWS command and presigned URL
                $command = $this->client->getCommand('PutObject', [
                    'Bucket'      => $this->bucket,
                    'Key'         => $key,
                    'ContentType' => $contentType,
                ]);

                $presignedRequest = $this->client->createPresignedRequest(
                    $command,
                    '+1 hours'
                );

                return [
                    'uid'          => $uid,
                    'type'         => $contentType,
                    'size'         => $size,
                    'key'          => $key,
                    'upload_url'   => (string) $presignedRequest->getUri(),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Confirm the upload by checking the file sizes and storing the metadata.
     */
    public function confirm($entity, $expires = '+4 hours')
    {
        $client = $this->client;
        $bucket = config('filesystems.disks.s3.bucket');
        $result = $client->headObject([
            'Bucket' => $bucket,
            'Key' => $entity['key'],
        ]);

        $command = $client->getCommand('GetObject', [
            'Bucket' => $bucket,
            'Key' => $entity->key,
        ]);

        $options = [
            'ResponseContentDisposition' => 'inline',
            'ResponseContentType' => $entity->type,
        ];

        $entity->e_tag = trim($result['ETag'], '"') ?? null;
        $entity->size = $result['ContentLength'] ?? null;
        $entity->expires_at = $expires;
        $entity->public_url = (string) $client->createPresignedRequest(
            $command,
            $expires,
            $options
        )->getUri();

        return $entity;
    }
}
