<?php

namespace App\Services\Admin;

use App\Enums\AccountStatus;
use App\Logging\AppLogger;
use App\Models\User;
use Illuminate\Support\Str;

class UserDeletionService
{
    public function __construct(protected AppLogger $logger)
    {
        $this->logger->setContext('UserDeletionService');
    }

    public function delete(User $user, string $mode = 'soft'): void
    {
        if ($mode === 'soft') {
            $this->softDelete($user);

            return;
        }

        $this->hardDelete($user);
    }

    public function softDelete(User $user): void
    {
        $user->email = $this->anonymiseEmail($user->email);
        $user->account_status = AccountStatus::DEACTIVATED;
        $user->password = bcrypt(Str::random(32));
        $user->save();

        $this->logger->info('User soft deleted', [
            'user_id' => $user->id,
            'user_uid' => $user->uid,
        ]);
    }

    public function toggleAccountStatus(User $user): void
    {
        if ($user->accountDeactivated()) {
            $user->account_status = AccountStatus::VERIFIED;
            $user->password = bcrypt(Str::random(32));
            $user->save();

            $this->logger->info('User reactivated', [
                'user_id' => $user->id,
                'user_uid' => $user->uid,
            ]);

            return;
        }

        $user->account_status = AccountStatus::DEACTIVATED;
        $user->password = bcrypt(Str::random(32));
        $user->save();

        $this->logger->info('User deactivated', [
            'user_id' => $user->id,
            'user_uid' => $user->uid,
        ]);
    }

    public function hardDelete(User $user): void
    {
        $user->forceDelete();

        $this->logger->info('User hard deleted', [
            'user_id' => $user->id,
            'user_uid' => $user->uid,
        ]);
    }

    protected function anonymiseEmail(string $email): string
    {
        $normalizedEmail = strtolower($email);
        $originalLocal = strstr($normalizedEmail, '@', true);
        $domain = strstr($normalizedEmail, '@');
        $date = now()->format('Ymd');

        return "deleted-{$date}-{$originalLocal}{$domain}";
    }
}
