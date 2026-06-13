<?php

namespace Database\Seeders;

use App\Enums\AccountStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SetupTestAccountsSeeder extends Seeder
{
    /**
     * Seed core test users and additional dummy users.
     */
    public function run(): void
    {
        $this->seedUsers(
            collect()->times(100, function (int $index) {
                return [
                    'name' => "Test User {$index}",
                    'email' => "test.user.{$index}@example.com",
                    'account_status' => $this->randomAccountStatusValue(),
                ];
            })->prepend([
                // This user has been listed as APP_ADMIN_EMAILS in .env giving it admin privileges.
                'name' => 'Luke Skywalker',
                'email' => 'skywalker@classermedia.com',
                'account_status' => AccountStatus::VERIFIED->value,
            ])
        );
    }

    /**
     * Seed user records with idempotent updates.
     */
    protected function seedUsers(Collection $users): void
    {
        $users->each(function (array $user): void {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'uid' => (string) Str::uuid(),
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'account_status' => $user['account_status'],
                    'password' => Hash::make('password1'),
                ]
            );
        });
    }

    protected function randomAccountStatusValue(): int
    {
        $statuses = array_map(
            static fn (AccountStatus $status): int => $status->value,
            AccountStatus::cases()
        );

        return $statuses[array_rand($statuses)];
    }
}
