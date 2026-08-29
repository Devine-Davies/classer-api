<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Jobs\Mail\MailUserPasswordReset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class AdminEmailBroadcastTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_composer_with_a_prefilled_template_and_recipient(): void
    {
        $admin = $this->createAdminUser();
        config()->set('classer.admin_email', $admin->email);

        $response = $this->actingAs($admin)->get(route('admin.email-broadcasts', [
            'template' => 'password_reset',
            'emails' => 'customer@example.com',
        ]));

        $response->assertOk()
            ->assertViewHas('prefilledTemplate', 'password_reset')
            ->assertViewHas('prefilledEmails', 'customer@example.com')
            ->assertSee('customer@example.com');
    }

    public function test_invalid_prefilled_template_is_ignored(): void
    {
        $admin = $this->createAdminUser();
        config()->set('classer.admin_email', $admin->email);

        $response = $this->actingAs($admin)->get(route('admin.email-broadcasts', [
            'template' => 'not-a-template',
        ]));

        $response->assertOk()->assertViewHas('prefilledTemplate', '');
    }

    public function test_admin_password_reset_broadcast_generates_token_and_dispatches_mail(): void
    {
        Bus::fake();
        $admin = $this->createAdminUser();
        $user = User::factory()->create([
            'account_status' => AccountStatus::VERIFIED,
            'password_reset_token' => null,
        ]);
        config()->set('classer.admin_email', $admin->email);

        $response = $this->actingAs($admin)->post(route('admin.email-broadcasts.queue'), [
            'template' => 'password_reset',
            'emails' => $user->email,
        ]);

        $response->assertRedirect(route('admin.email-broadcasts'))
            ->assertSessionHas('emailBroadcastResult.total_sent', 1);
        $this->assertNotNull($user->fresh()->password_reset_token);
        Bus::assertDispatched(MailUserPasswordReset::class);
    }

    public function test_password_reset_broadcast_skips_inactive_users(): void
    {
        Bus::fake();
        $admin = $this->createAdminUser();
        $user = User::factory()->create([
            'account_status' => AccountStatus::INACTIVE,
            'password_reset_token' => null,
        ]);
        config()->set('classer.admin_email', $admin->email);

        $response = $this->actingAs($admin)->post(route('admin.email-broadcasts.queue'), [
            'template' => 'password_reset',
            'emails' => $user->email,
        ]);

        $response->assertRedirect(route('admin.email-broadcasts'))
            ->assertSessionHas('emailBroadcastResult.total_sent', 0)
            ->assertSessionHas('emailBroadcastResult.ineligible', [$user->email]);
        $this->assertNull($user->fresh()->password_reset_token);
        Bus::assertNotDispatched(MailUserPasswordReset::class);
    }

    public function test_admin_can_preview_recipient_eligibility(): void
    {
        $admin = $this->createAdminUser();
        $verifiedUser = User::factory()->create(['account_status' => AccountStatus::VERIFIED]);
        $inactiveUser = User::factory()->create(['account_status' => AccountStatus::INACTIVE]);
        config()->set('classer.admin_email', $admin->email);

        $response = $this->actingAs($admin)->postJson(route('admin.email-broadcasts.preview'), [
            'template' => 'password_reset',
            'emails' => implode("\n", [$verifiedUser->email, $inactiveUser->email, 'missing@example.com']),
        ]);

        $response->assertOk()->assertExactJson([
            'recipients' => 3,
            'eligible' => 1,
            'ineligible' => 1,
            'not_found' => 1,
        ]);
    }

    public function test_preview_rejects_invalid_recipient_input(): void
    {
        $admin = $this->createAdminUser();
        config()->set('classer.admin_email', $admin->email);

        $response = $this->actingAs($admin)->postJson(route('admin.email-broadcasts.preview'), [
            'template' => 'password_reset',
            'emails' => 'not-an-email',
        ]);

        $response->assertUnprocessable()
            ->assertExactJson(['message' => 'Enter a valid template and recipient list.']);
    }

    private function createAdminUser(): User
    {
        return User::factory()->create([
            'email' => 'admin.'.uniqid().'@example.com',
            'account_status' => AccountStatus::VERIFIED,
        ]);
    }
}
