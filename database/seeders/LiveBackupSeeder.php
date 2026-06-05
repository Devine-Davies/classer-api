<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\PersonalAccessToken;

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
        $json = file_get_contents('./database/seeders/livebackup-data/02-06-2026_u329348820_classer_api.json');
        $data = json_decode($json, true);
        foreach ($data as $obj) {
            if ($obj['type'] == 'table') {
                if ($obj['name'] == 'users') {
                    $this->seedUsers($obj['data']);
                }

                if ($obj['name'] == 'recorder') {
                    $this->recorder($obj['data']);
                }

                if ($obj['name'] == 'personal_access_tokens') {
                    $this->personalAccessTokens($obj['data']);
                }
            }
        }
    }

    /**
     * Seed the users
     */
    public function seedUsers($users) {
        foreach ($users as $user) {
            // This field is deprecated and should not be used, need to be removed
            unset($user['logged_in_at']);

            // if password is empty, set it to a default value
            if (empty($user['password'])) {
                $user['password'] = Hash::make(Str::random(32)); // hashed value for 'password'
            }

            User::create($user);
        }
    }

    /**
     * Seed the Recorder Model
     */
    public function recorder($data) {
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
            if (!empty($createdAt)) {
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
            if ($uid !== null && !isset($validUserIds[(int) $uid])) {
                $record['uid'] = null;
            }

            return $record;
        }, $records);

        // Normalize duplicate IDs from backup exports so each PK appears once per import.
        $dedupedRecords = [];
        foreach ($records as $record) {
            if (!empty($record['id'])) {
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
    public function personalAccessTokens($data) {
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
                'last_used_at' => !empty($token['last_used_at'])
                    ? Carbon::parse($token['last_used_at'])->format('Y-m-d H:i:s')
                    : null,
                'expires_at' => !empty($token['expires_at'])
                    ? Carbon::parse($token['expires_at'])->format('Y-m-d H:i:s')
                    : null,
                'created_at' => !empty($token['created_at'])
                    ? Carbon::parse($token['created_at'])->format('Y-m-d H:i:s')
                    : now()->format('Y-m-d H:i:s'),
                'updated_at' => !empty($token['updated_at'])
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


