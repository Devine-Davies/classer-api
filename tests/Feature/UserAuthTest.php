<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
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
        $response = $this->postJson('/api/auth/register', [
            'name' => $testUser['name'],
            'email' => $testUser['email'],
        ]);

        // Assert registration response
        $response->assertStatus(201)
            ->assertJsonStructure([
                'message'
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

        $login->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'token'
            ]);
    }

    public function test_user_can_auto_login()
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

        // Attempt to auto-login with the user token
        $autoLogin = $this->getJson('/api/auth/auto-login', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $autoLogin->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'token'
            ]);
    }

    public function test_user_can_logout()
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
        $token = $this->postJson('/api/auth/login', [
            'email' => $testUser['email'],
            'password' => $testPassword,
        ])->json('token');

        // Attempt to logout with the user token
        $this->postJson('/api/auth/logout', [], [
            'Authorization' => 'Bearer ' . $token,
        ])->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Logged out'
            ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'token' => hash('sha256', $token),
        ]);

        // Attempt to auto-login with the user token after logout
        $this->getJson('/api/auth/auto-login', [
            'Authorization' => 'Bearer ' . $token,
        ])->assertStatus(401)
            ->assertJsonStructure([
                'status',
                'message',
                'token'
            ]);
    }

    public function test_user_can_forgot_password_and_can_reset_it()
    {
        $testPassword = 'password123';
        $testUser = [
            'name' => 'Rhys Devine',
            'email' => 'rhys@example.com',
        ];

        // Register the user
        $response = $this->postJson('/api/auth/register', [
            'name' => $testUser['name'],
            'email' => $testUser['email'],
        ]);

        // Update the user account to simulate verification
        $this->updateUserAccount($testUser['email'], [
            'account_status' => \App\Enums\AccountStatus::VERIFIED,
            'password' => bcrypt($testPassword),
        ]);

        $userToken = $response->json('token');

        // Simulate password reset request
        $response = $this->postJson('/api/auth/password/forgot', [
            'email' => $testUser['email'],
        ]);

        // Assert forgot password response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'message'
            ]);

        // get the reset token from the db
        $user = User::where('email', $testUser['email'])->first();
        $resetToken = $user->password_reset_token;

        // Simulate password reset
        $resetResponse = $this->postJson('/api/auth/password/reset', [
            'token' => $resetToken,
            'password' => 'newPassword123',
            'passwordConfirmation' => 'newPassword123',
        ]);

        // Assert reset password response
        $resetResponse->assertStatus(200)
            ->assertJsonStructure([
                'message'
            ]);

        // Attempt to login with the new password
        $login = $this->postJson('/api/auth/login', [
            'email' => $testUser['email'],
            'password' => 'newPassword123',
        ]);

        $login->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'token'
            ]);
    }

    public function test_user_can_update_password()
    {
        $testPassword = 'password123';
        $testUser = [
            'name' => 'Rhys Devine',
            'email' => 'rhys@example.com',
        ];

        // Register the user
        $response = $this->postJson('/api/auth/register', [
            'name' => $testUser['name'],
            'email' => $testUser['email'],
        ]);

        // Update the user account to simulate verification
        $this->updateUserAccount($testUser['email'], [
            'account_status' => \App\Enums\AccountStatus::VERIFIED,
            'password' => bcrypt($testPassword),
        ]);

        // login the user
        $login = $this->postJson('/api/auth/login', [
            'email' => $testUser['email'],
            'password' => $testPassword,
        ]);

        $authToken = $login->json('token');

        // Attempt to update password
        $updateResponse = $this->patchJson('/api/user/update-password', [
            'password' => $testPassword,
            'newPassword' => 'newPassword123',
            'passwordConfirmation' => 'newPassword123',
        ], [
            'Authorization' => 'Bearer ' . $authToken,
        ]);

        // Assert update password response
        $updateResponse->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message'
            ]);

        // Attempt to login with the new password
        $login = $this->postJson('/api/auth/login', [
            'email' => $testUser['email'],
            'password' => 'newPassword123',
        ]);

        $login->assertStatus(200);
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
