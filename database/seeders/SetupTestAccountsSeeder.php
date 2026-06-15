<?php

namespace Database\Seeders;

use App\Enums\AccountStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class SetupTestAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $users = collect()
            ->times(10, function (int $index): array {
                return [
                    'name' => "Test User {$index}",
                    'email' => "test.user.{$index}@example.com",
                    'account_status' => $this->randomAccountStatus(),
                ];
            })
            ->prepend([
                'name' => 'Luke Skywalker',
                'email' => 'skywalker@classermedia.com',
                'account_status' => AccountStatus::VERIFIED,
            ]);

        $this->seedUsers($users);
    }

    protected function seedUsers(Collection $users): void
    {
        $users->each(function (array $user): void {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'account_status' => $user['account_status'],
                    'password' => 'password1',
                ]
            );
        });
    }

    protected function randomAccountStatus(): AccountStatus
    {
        return fake()->randomElement(AccountStatus::cases());
    }
}
