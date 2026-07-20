<?php

namespace Tests\End2End\Api;

use App\Jobs\MailUserReviewReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\End2End\Api\Concerns\BuildsApiActors;
use Tests\TestCase;

class AdminApiTest extends TestCase
{
    use BuildsApiActors;
    use RefreshDatabase;

    public function test_admin_logs_and_bulk_mail_routes_require_authentication(): void
    {
        $this->getJson('/api/admin/logs')->assertStatus(401);
        $this->postJson('/api/admin/bulk-mails/queue')->assertStatus(401);
    }

    public function test_authenticated_user_can_fetch_admin_log_listing(): void
    {
        $this->actingAsUser(abilities: ['user']);

        $response = $this->getJson('/api/admin/logs');

        $response->assertOk()
            ->assertJsonIsArray();
    }

    public function test_logs_endpoint_rejects_non_log_extensions(): void
    {
        $this->actingAsUser(abilities: ['user']);

        $response = $this->getJson('/api/admin/logs/not-a-log.txt');

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Invalid log file.');
    }

    public function test_bulk_mail_queue_requires_configured_templates(): void
    {
        $this->actingAsUser(abilities: ['user']);
        config()->set('classer.admin_bulk_mail_templates', []);

        $response = $this->postJson('/api/admin/bulk-mails/queue', [
            'template' => 'review-reminder',
            'emails' => 'nobody@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    public function test_bulk_mail_queue_dispatches_jobs_for_matching_users(): void
    {
        Queue::fake();
        $this->actingAsUser(abilities: ['user']);

        $recipient = $this->makeVerifiedUser([
            'email' => 'bulk-mail-recipient@example.com',
        ]);

        config()->set('classer.admin_bulk_mail_templates', [
            'review-reminder' => [
                'label' => 'Review Reminder',
                'job' => MailUserReviewReminder::class,
                'account_statuses' => [1],
            ],
        ]);

        $response = $this->postJson('/api/admin/bulk-mails/queue', [
            'template' => 'review-reminder',
            'emails' => $recipient->email,
        ]);

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.total_sent', 1);

        Queue::assertPushed(MailUserReviewReminder::class, 1);
    }
}
