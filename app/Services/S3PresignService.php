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
     * Generate presigned URLs for uploading files to S3.
     */
    public function generateUploadUrls(array $entities): array
    {
        $groupId = Str::uuid();
        $urls = collect($entities)->map(function ($entity) use ($groupId) {
            $uid = $entity['uid'];
            $sourceFile = $entity['sourceFile'];
            $contentType = $entity['contentType'];
            $extension = pathinfo($sourceFile, PATHINFO_EXTENSION);

            $name = Str::uuid();
            $filename = "$name.$extension";
            $cloudShareDirectory = config('classer.cloud_share_directory', 'cloud-share');
            $key = "{$cloudShareDirectory}/{$groupId}/{$filename}";
            $command = $this->client->getCommand('PutObject', [
                'Bucket' => $this->bucket,
                'Key' => $key,
                'ContentType' => $contentType
            ]);

            $presignedRequest = $this->client->createPresignedRequest(
                $command,
                '+1 hours'
            );

            return [
                'uid' => $uid,
                'type' => $contentType,
                'key' => $key,
                'upload_url' => $presignedRequest->getUri(),
            ];
        });

        return $urls->values()->all();
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
