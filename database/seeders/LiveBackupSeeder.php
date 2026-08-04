<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Seeder for live backup data
 *
 * php artisan db:seed --class=LiveBackupSeeder
 */
class LiveBackupSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // // read json file
        // $json = file_get_contents('database\seeders\livebackup-data\u329348820_classer_api.json');
        $json = file_get_contents('./database/seeders/livebackup-data/03-08-2026_u329348820_classer_api.json');
        $data = json_decode($json, true);

        if (! is_array($data)) {
            return;
        }

        $tables = $this->extractBackupTables($data);

        // Base user data first so FK-linked tables can be imported safely.
        $this->seedUsers($tables['users'] ?? []);

        // Cloud domain tables.
        $this->seedGenericTable('cloud_entities', $tables['cloud_entities'] ?? []);
        $this->seedGenericTable('cloud_share', $tables['cloud_share'] ?? []);
        $this->seedGenericTable('cloud_share_jobs', $tables['cloud_share_jobs'] ?? []);

        // Priority domain tables for checkout/billing continuity.
        $this->seedGenericTable('products', $tables['products'] ?? []);
        $this->seedGenericTable('plans', $tables['plans'] ?? []);
        $this->seedGenericTable('catalog_items', $tables['catalog_items'] ?? []);
        $this->seedGenericTable('discount_codes', $tables['discount_codes'] ?? []);
        $this->seedGenericTable('orders', $tables['orders'] ?? []);
        $this->seedGenericTable('order_items', $tables['order_items'] ?? []);
        $this->seedGenericTable('order_payments', $tables['order_payments'] ?? []);
        $this->seedGenericTable('stripe_events', $tables['stripe_events'] ?? []);
        $this->seedGenericTable('discount_code_redemptions', $tables['discount_code_redemptions'] ?? []);
        $this->seedGenericTable('user_subscriptions', $tables['user_subscriptions'] ?? []);
        $this->seedGenericTable('user_cloud_usages', $tables['user_cloud_usages'] ?? []);
        $this->seedGenericTable('jobs', $tables['jobs'] ?? []);
        $this->seedGenericTable('logs', $tables['logs'] ?? []);
        $this->seedGenericTable('password_reset_tokens', $tables['password_reset_tokens'] ?? []);

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
