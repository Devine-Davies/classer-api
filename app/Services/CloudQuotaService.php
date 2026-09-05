<?php

namespace App\Services;

use App\Enums\CloudStorageKind;
use App\Exceptions\CloudStorageQuotaExceededException;
use App\Models\User;
use App\Models\UserCloudUsage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CloudQuotaService
{
    public function canReserve(User $user, CloudStorageKind $kind, int $bytes): bool
    {
        return $bytes >= 0
            && $this->quota($user, $kind) > 0
            && $bytes <= $this->remaining($user, $kind);
    }

    public function reserve(User $user, CloudStorageKind $kind, int $bytes): void
    {
        DB::transaction(function () use ($user, $kind, $bytes): void {
            $usage = $this->lockUsage($user);
            $quota = $this->quota($user, $kind);
            $used = (int) $usage->{$kind->usageColumn()};

            if ($bytes < 0 || $quota <= 0 || $bytes > $quota - $used) {
                throw new CloudStorageQuotaExceededException($bytes);
            }

            $usage->update([
                $kind->usageColumn() => $used + $bytes,
            ]);
        });
    }

    public function release(User $user, CloudStorageKind $kind, int $bytes): void
    {
        DB::transaction(function () use ($user, $kind, $bytes): void {
            $usage = $this->lockUsage($user);
            $used = (int) $usage->{$kind->usageColumn()};

            $usage->update([
                $kind->usageColumn() => max(0, $used - max(0, $bytes)),
            ]);
        });
    }

    public function remaining(User $user, CloudStorageKind $kind): int
    {
        $quota = $this->quota($user, $kind);
        $used = (int) UserCloudUsage::query()
            ->where('user_id', $user->uid)
            ->value($kind->usageColumn());

        return max(0, $quota - $used);
    }

    public function quota(User $user, CloudStorageKind $kind): int
    {
        return $user->subscription?->plan?->entitlementQuota($kind->capability()) ?? 0;
    }

    private function lockUsage(User $user): UserCloudUsage
    {
        UserCloudUsage::firstOrCreate(
            ['user_id' => $user->uid],
            [
                'uid' => (string) Str::uuid(),
                'share_usage' => 0,
                'backup_usage' => 0,
            ]
        );

        return UserCloudUsage::query()
            ->where('user_id', $user->uid)
            ->lockForUpdate()
            ->firstOrFail();
    }
}
