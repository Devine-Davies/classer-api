<?php

namespace Tests\End2End\Api;

use App\Enums\AccountStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserIndexApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_with_user_ability_can_fetch_profile(): void
    {
        $user = User::create([
            'uid' => (string) Str::uuid(),
            'name' => 'End2End User',
            'email' => 'e2e.user@example.com',
            'password' => bcrypt('password123'),
            'account_status' => AccountStatus::VERIFIED,
        ]);

        Sanctum::actingAs($user, ['user']);

        $response = $this->getJson('/api/user');

        $response->assertOk()->assertJsonPath('uid', $user->uid)
            ->assertJsonPath('name', $user->name)
            ->assertJsonPath('email', $user->email)
            ->assertJsonPath('accountStatus', AccountStatus::VERIFIED->value)
            ->assertJsonPath('accountStatusLabel', 'Verified')
            ->assertJsonPath('accountStatusTone', 'success')
            ->assertJsonStructure([
                'uid',
                'name',
                'email',
                'dob',
                'createdAt',
                'updatedAt',
                'accountStatus',
                'accountStatusLabel',
                'accountStatusTone',
                'subscription',
                'cloudUsage',
            ]);
    }
}
