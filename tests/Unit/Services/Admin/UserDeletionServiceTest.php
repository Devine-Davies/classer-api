<?php

namespace Tests\Unit\Services\Admin;

use App\Enums\AccountStatus;
use App\Models\User;
use App\Services\Admin\UserDeletionService;
use Tests\TestCase;

class UserDeletionServiceTest extends TestCase
{
    public function test_it_toggles_a_user_account_status_between_deactivated_and_verified(): void
    {
        $user = User::factory()->create([
            'account_status' => AccountStatus::VERIFIED,
        ]);

        $service = app(UserDeletionService::class);

        $service->toggleAccountStatus($user);
        $user->refresh();

        $this->assertTrue($user->accountDeactivated());

        $service->toggleAccountStatus($user);
        $user->refresh();

        $this->assertTrue($user->accountVerified());
    }
}
