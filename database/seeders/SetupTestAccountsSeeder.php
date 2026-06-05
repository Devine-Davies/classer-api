<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SetupTestAccountsSeeder extends Seeder
{
    /**
     * Seed core test users and additional dummy users.
     */
    public function run(): void
    {
        $testUsers = [
            [
                // This user has been listed as APP_ADMIN_EMAILS in .env giving it admin privileges.
                'uid' => 'aaaa4b98-8654-4815-93a3-000000000001',
                'name' => 'Rhys(RD) Devine-Davies',
                'email' => 'skywalker@classermedia.com',
            ],
            [
                'uid' => 'bbbb22dc-7abe-4999-93a3-000000000002',
                'name' => 'Rhys() Devine-Davies',
                'email' => 'skywalker+1@classer.com',
            ],
            [
                'uid' => 'cccc33b8-9a1e-4c6b-93a3-000000000003',
                'name' => 'Rhys(RD) Devine-Davies',
                'email' => 'skywalker+2@example.com',
            ],
        ];

        $dummyUsers = [
            [
                'uid' => 'dddd44b8-9a1e-4c6b-93a3-000000000004',
                'name' => 'Dummy Order User 1',
                'email' => 'dummy.order.1@example.com',
            ],
            [
                'uid' => 'eeee55b8-9a1e-4c6b-93a3-000000000005',
                'name' => 'Dummy Order User 2',
                'email' => 'dummy.order.2@example.com',
            ],
            [
                'uid' => 'ffff66b8-9a1e-4c6b-93a3-000000000006',
                'name' => 'Dummy Order User 3',
                'email' => 'dummy.order.3@example.com',
            ],
            [
                'uid' => '111177b8-9a1e-4c6b-93a3-000000000007',
                'name' => 'Dummy Order User 4',
                'email' => 'dummy.order.4@example.com',
            ],
            [
                'uid' => '222288b8-9a1e-4c6b-93a3-000000000008',
                'name' => 'Dummy Order User 5',
                'email' => 'dummy.order.5@example.com',
            ],
        ];

        $this->seedUsers($testUsers);
        $this->seedUsers($dummyUsers);
    }

    /**
     * Seed user records with idempotent updates.
     */
    protected function seedUsers(array $users): void
    {
        collect($users)->each(function (array $user): void {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'uid' => $user['uid'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'password' => Hash::make('password1'),
                    'account_status' => 1,
                ]
            );
        });
    }
}