<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class CloudShareTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_login_successfully()
    {
        $testPassword = 'password123';
        $testUser = [
            'name' => 'Rhys Devine',
            'email' => 'rhys@example.com',
        ];

        // Register the user
        $this->postJson('/api/auth/register', [
            'name' => $testUser['name'],
            'email' => $testUser['email'],
        ]);

        // Update the user account to simulate verification
        $this->updateUserAccount($testUser['email'], [
            'account_status' => \App\Enums\AccountStatus::VERIFIED,
            'password' => bcrypt($testPassword),
        ]);

        // Attempt to login with the registered user
        $login = $this->postJson('/api/auth/login', [
            'email' => $testUser['email'],
            'password' => $testPassword,
        ]);

        $token = $login->json('token');

        // Assert login response
        $presignPayload = [
            'resourceId' => 'd282ba9b-e5dc-4957-baef-dfae20cf95ca',
            'entities' => [
                [
                    'uid' => 'd282ba9b-e5dc-4957-baef-dfae20sf95ca',
                    'sourceFile' => 'C:/Users/Rhys Devine-Davies/Videos/Single Video/GOPRO_09/GH011029  .MP4',
                    'contentType' => 'video/mp4',
                    'size' => 102400,
                ],
                [
                    'uid' => 's282ba9b-e5dc-4957-baef-dfae20sf95ca',
                    'sourceFile' => 'C:\\Users\\Rhys Devine-Davies\\Pictures\\Classer\\thumbnails\\PYNwRgrgNg7gdABjgZjgNgCwIB7LQoA=.jpeg',
                    'contentType' => 'image/jpeg',
                    'size' => 102400,
                ],
            ],
        ];

        $presign = $this->postJson(
            '/api/cloud/share/presign',
            $presignPayload,
            ['Authorization' => "Bearer $token"]
        );
    }

    protected function updateUserAccount($email, $attributes)
    {
        $user = User::where('email', $email)->first();
        if ($user) {
            foreach ($attributes as $key => $value) {
                $user->$key = $value;
            }
            $user->save();
        }
    }
}
