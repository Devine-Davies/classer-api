<?php

namespace Database\Seeders;

use App\Enums\CloudEntityRole;
use App\Enums\CloudShareStatus;
use App\Enums\CloudStorageKind;
use App\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\Mime\MimeTypes;

/**
 * Seeder for live backup data
 *
 * php artisan db:seed --class=LiveBackupSeeder
 */
class LiveBackupSeeder extends Seeder
{
    private const BACKUP_PATH = 'database/seeders/livebackup-data/04-09-2026_u329348820_classer_api.json';

    private const MIME_TYPES_BY_EXTENSION = [
        'avi' => 'video/x-msvideo',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'mov' => 'video/quicktime',
        'mp4' => 'video/mp4',
        'ogg' => 'video/ogg',
        'png' => 'image/png',
        'webp' => 'image/webp',
    ];

    private const IMPORT_ORDER = [
        'products',
        'plans',
        'catalog_items',
        'discount_codes',
        'orders',
        'order_items',
        'order_payments',
        'discount_code_redemptions',
        'stripe_events',
        'user_subscriptions',
        'user_cloud_usages',
        'cloud_share',
        'cloud_backups',
        'cloud_entities',
        'cloud_share_jobs',
        'jobs',
        'failed_jobs',
        'logs',
        'password_reset_tokens',
    ];

    private const EXCLUDED_TABLES = [
        'migrations',
        'users',
        'recorder',
        'personal_access_tokens',
    ];

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $data = json_decode(file_get_contents(base_path(self::BACKUP_PATH)), true);

        if (! is_array($data)) {
            return;
        }

        $tables = $this->extractBackupTables($data);

        // Base user data first so FK-linked tables can be imported safely.
        $this->seedUsers($tables['users'] ?? []);

        foreach (self::IMPORT_ORDER as $table) {
            $this->seedGenericTable($table, $tables[$table] ?? []);
            unset($tables[$table]);
        }

        // Import future backup tables automatically when they exist in the local schema.
        foreach ($tables as $table => $rows) {
            if (in_array($table, self::EXCLUDED_TABLES, true)) {
                continue;
            }

            $this->seedGenericTable($table, $rows);
        }

        $this->seedLegacyPlanEntitlements();

        // Existing specialized imports.
        $this->recorder($tables['recorder'] ?? []);
        $this->personalAccessTokens($tables['personal_access_tokens'] ?? []);
    }

    /**
     * Build a table-name keyed map from backup payload.
     *
     * @param  array<int, mixed>  $data
     * @return array<string, array<int, mixed>>
     */
    protected function extractBackupTables(array $data): array
    {
        $tables = [];

        foreach ($data as $obj) {
            if (! is_array($obj)) {
                continue;
            }

            if (($obj['type'] ?? null) !== 'table') {
                continue;
            }

            $name = (string) ($obj['name'] ?? '');

            if ($name === '') {
                continue;
            }

            $rows = $obj['data'] ?? [];
            $tables[$name] = is_array($rows) ? $rows : [];
        }

        return $tables;
    }

    /**
     * Generic import for backup tables with schema-aware filtering.
     *
     * @param  array<int, mixed>  $rows
     */
    protected function seedGenericTable(string $table, array $rows): void
    {
        if ($rows === [] || ! Schema::hasTable($table)) {
            return;
        }

        $columns = Schema::getColumnListing($table);

        if ($columns === []) {
            return;
        }

        $uniqueBy = in_array('id', $columns, true)
            ? 'id'
            : (in_array('uid', $columns, true) ? 'uid' : $columns[0]);

        $payload = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $row = $this->normalizeBackupRow($table, $row);
            $record = [];

            foreach ($columns as $column) {
                if (! array_key_exists($column, $row)) {
                    continue;
                }

                $record[$column] = $this->normalizeBackupValue($row[$column], $column);
            }

            if (! array_key_exists($uniqueBy, $record) || $record[$uniqueBy] === null || $record[$uniqueBy] === '') {
                continue;
            }

            $payload[] = $record;
        }

        if ($payload === []) {
            return;
        }

        $updateColumns = array_values(array_diff($columns, [$uniqueBy]));

        foreach (array_chunk($payload, 400) as $chunk) {
            DB::table($table)->upsert($chunk, [$uniqueBy], $updateColumns);
        }
    }

    /**
     * Normalize legacy table shapes before filtering against the current schema.
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function normalizeBackupRow(string $table, array $row): array
    {
        if ($table === 'cloud_share') {
            $row['status'] = CloudShareStatus::ACTIVE->value;
        }

        if ($table === 'cloud_entities') {
            $extension = strtolower(pathinfo((string) ($row['key'] ?? ''), PATHINFO_EXTENSION));
            $row['mime_type'] = self::MIME_TYPES_BY_EXTENSION[$extension]
                ?? MimeTypes::getDefault()->getMimeTypes($extension)[0]
                ?? 'application/octet-stream';
            $row['object_role'] = $this->cloudEntityRoleForMimeType($row['mime_type']);
        }

        if ($table === 'user_cloud_usages') {
            $row['share_usage'] = (int) ($row['total_usage'] ?? 0);
            $row['backup_usage'] = 0;
            unset($row['total_usage']);
        }

        return $row;
    }

    protected function seedLegacyPlanEntitlements(): void
    {
        if (! Schema::hasTable('plan_entitlements')) {
            return;
        }

        $capabilitiesByPlanType = [
            'cloud_share' => CloudStorageKind::SHARE,
            'cloud_backup' => CloudStorageKind::BACKUP,
            'backup_storage' => CloudStorageKind::BACKUP,
        ];

        Plan::query()->each(function (Plan $plan) use ($capabilitiesByPlanType): void {
            $kind = $capabilitiesByPlanType[$plan->type] ?? null;

            if (! $kind) {
                return;
            }

            $plan->entitlements()->firstOrCreate(
                ['capability' => $kind->capability()],
                ['quota' => (int) ($plan->quota ?? 0)]
            );
        });
    }

    protected function cloudEntityRoleForMimeType(string $mimeType): ?string
    {
        if (str_starts_with($mimeType, 'video/')) {
            return CloudEntityRole::VIDEO->value;
        }

        if (str_starts_with($mimeType, 'image/')) {
            return CloudEntityRole::THUMBNAIL->value;
        }

        return null;
    }

    /**
     * Normalize imported scalar/JSON/timestamp-like values for DB upsert.
     */
    protected function normalizeBackupValue(mixed $value, string $column): mixed
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }

        if ($value === '') {
            return null;
        }

        $isTimestampLike = str_ends_with($column, '_at')
            || in_array($column, ['expiration_date', 'cancellation_date', 'redeemed_at'], true);

        if ($isTimestampLike && is_string($value)) {
            try {
                return Carbon::parse($value)->format('Y-m-d H:i:s');
            } catch (\Throwable) {
                return $value;
            }
        }

        return $value;
    }

    /**
     * Seed the users
     */
    public function seedUsers($users)
    {
        if (empty($users) || ! Schema::hasTable('users')) {
            return;
        }

        $columns = Schema::getColumnListing('users');

        if ($columns === []) {
            return;
        }

        $payload = [];

        foreach ($users as $user) {
            if (! is_array($user)) {
                continue;
            }

            // This field is deprecated and should not be used, need to be removed
            unset($user['logged_in_at']);

            // if password is empty, set it to a default value
            if (empty($user['password'])) {
                $user['password'] = Hash::make(Str::random(32)); // hashed value for 'password'
            }

            $record = [];
            foreach ($columns as $column) {
                if (! array_key_exists($column, $user)) {
                    continue;
                }

                $record[$column] = $this->normalizeBackupValue($user[$column], $column);
            }

            if (! array_key_exists('id', $record) || empty($record['id'])) {
                continue;
            }

            $payload[] = $record;
        }

        if ($payload === []) {
            return;
        }

        $updateColumns = array_values(array_diff($columns, ['id']));

        foreach (array_chunk($payload, 400) as $chunk) {
            DB::table('users')->upsert($chunk, ['id'], $updateColumns);
        }
    }

    /**
     * Seed the Recorder Model
     */
    public function recorder($data)
    {
        if (empty($data)) {
            return;
        }

        // Backup snapshots are not always perfectly relationally consistent:
        // - some users may have been deleted after recorder rows were created,
        // - or the export may contain recorder rows whose user row is not present.
        // The recorder.uid column has a FK to users.id, so importing these rows as-is
        // would fail the whole seed with a foreign key violation.
        // We precompute valid user IDs and null unknown uids to preserve the event row
        // while safely dropping only the orphaned user link.
        $validUserIds = array_flip(
            DB::table('users')->pluck('id')->map(fn ($id) => (int) $id)->all()
        );

        $records = array_map(function ($record) {
            $metadata = $record['metadata'] ?? null;
            if (is_array($metadata) || is_object($metadata)) {
                $metadata = json_encode($metadata);
            }

            // recorder.metadata is TEXT (max 65,535 bytes) so cap payload size.
            if (is_string($metadata) && strlen($metadata) > 65535) {
                $metadata = mb_strcut($metadata, 0, 65535, 'UTF-8');
            }

            $createdAt = $record['created_at'] ?? null;
            if (! empty($createdAt)) {
                $createdAt = Carbon::parse($createdAt)->format('Y-m-d H:i:s');
            } else {
                $createdAt = now()->format('Y-m-d H:i:s');
            }

            return [
                'id' => $record['id'] ?? null,
                'uid' => $record['uid'] ?? null,
                'type' => $record['type'] ?? null,
                'code' => $record['code'] ?? null,
                'metadata' => $metadata,
                'created_at' => $createdAt,
            ];
        }, $data);

        $records = array_map(function (array $record) use ($validUserIds) {
            $uid = $record['uid'];
            if ($uid !== null && ! isset($validUserIds[(int) $uid])) {
                $record['uid'] = null;
            }

            return $record;
        }, $records);

        // Normalize duplicate IDs from backup exports so each PK appears once per import.
        $dedupedRecords = [];
        foreach ($records as $record) {
            if (! empty($record['id'])) {
                $dedupedRecords[(string) $record['id']] = $record;

                continue;
            }

            $dedupedRecords[] = $record;
        }

        $records = array_values($dedupedRecords);

        foreach (array_chunk($records, 400) as $chunk) {
            DB::table('recorder')->upsert(
                $chunk,
                ['id'],
                ['uid', 'type', 'code', 'metadata', 'created_at']
            );
        }
    }

    /**
     * Seed the Access Tokens
     */
    public function personalAccessTokens($data)
    {
        if (empty($data)) {
            return;
        }

        $tokens = array_map(function ($token) {
            return [
                'id' => $token['id'] ?? null,
                'tokenable_type' => $token['tokenable_type'] ?? null,
                'tokenable_id' => $token['tokenable_id'] ?? null,
                'name' => $token['name'] ?? 'API TOKEN',
                'token' => $token['token'] ?? null,
                'abilities' => isset($token['abilities'])
                    ? (is_array($token['abilities']) ? json_encode($token['abilities']) : $token['abilities'])
                    : json_encode(['user']),
                'last_used_at' => ! empty($token['last_used_at'])
                    ? Carbon::parse($token['last_used_at'])->format('Y-m-d H:i:s')
                    : null,
                'expires_at' => ! empty($token['expires_at'])
                    ? Carbon::parse($token['expires_at'])->format('Y-m-d H:i:s')
                    : null,
                'created_at' => ! empty($token['created_at'])
                    ? Carbon::parse($token['created_at'])->format('Y-m-d H:i:s')
                    : now()->format('Y-m-d H:i:s'),
                'updated_at' => ! empty($token['updated_at'])
                    ? Carbon::parse($token['updated_at'])->format('Y-m-d H:i:s')
                    : now()->format('Y-m-d H:i:s'),
            ];
        }, $data);

        foreach (array_chunk($tokens, 400) as $chunk) {
            DB::table('personal_access_tokens')->upsert(
                $chunk,
                ['id'],
                ['tokenable_type', 'tokenable_id', 'name', 'token', 'abilities', 'last_used_at', 'expires_at', 'created_at', 'updated_at']
            );
        }
    }
}
