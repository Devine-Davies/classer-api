<?php

namespace Tests\End2End\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SystemSiteWebhookApiTest extends TestCase
{
    use RefreshDatabase;

    protected string $releaseFixturePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->releaseFixturePath = resource_path('releases.json');
        File::put($this->releaseFixturePath, json_encode([
            'windows-x64' => [
                '1.0.0' => [
                    'version' => '1.0.1',
                    'required' => false,
                ],
            ],
        ], JSON_THROW_ON_ERROR));
    }

    protected function tearDown(): void
    {
        if (File::exists($this->releaseFixturePath)) {
            File::delete($this->releaseFixturePath);
        }

        parent::tearDown();
    }

    public function test_versions_endpoint_returns_release_payload_for_matching_headers(): void
    {
        $response = $this->withHeaders([
            'x-app-version' => '1.0.0',
            'x-app-platform' => 'windows',
            'x-app-architecture' => 'x64',
        ])->getJson('/api/versions');

        $response->assertOk()
            ->assertJsonPath('version', '1.0.1')
            ->assertJsonPath('required', false);
    }

    public function test_versions_endpoint_returns_error_payload_for_unknown_platform(): void
    {
        $response = $this->withHeaders([
            'x-app-version' => '1.0.0',
            'x-app-platform' => 'linux',
            'x-app-architecture' => 'x64',
        ])->getJson('/api/versions');

        $response->assertOk()
            ->assertSeeText('@error');
    }

    public function test_actions_camera_matcher_requires_answers_payload(): void
    {
        config()->set('services.recaptcha.enabled', false);

        $response = $this->postJson('/api/site/actions-camera-matcher', []);

        $response->assertStatus(422);
    }

    public function test_actions_camera_matcher_accepts_answers_when_recaptcha_is_disabled(): void
    {
        config()->set('services.recaptcha.enabled', false);

        $response = $this->postJson('/api/site/actions-camera-matcher', [
            'grc' => 'bad-token',
            'answers' => ['style' => 'cinematic'],
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Action Camera Matcher stored');
    }

    public function test_actions_camera_matcher_accepts_valid_recaptcha(): void
    {
        config()->set('services.recaptcha.enabled', true);
        config()->set('services.recaptcha.url', 'https://recaptcha.local/verify');
        config()->set('services.recaptcha.threshold', 0.5);

        Http::fake([
            'recaptcha.local/*' => Http::response([
                'success' => true,
                'score' => 0.9,
            ], 200),
        ]);

        $response = $this->postJson('/api/site/actions-camera-matcher', [
            'grc' => 'good-token',
            'answers' => ['style' => 'cinematic'],
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Action Camera Matcher stored');
    }

    public function test_stripe_webhook_rejects_invalid_signature(): void
    {
        $response = $this->withHeaders([
            'Stripe-Signature' => 'invalid-signature',
        ])->postJson('/api/stripe/webhook', [
            'id' => 'evt_test_invalid',
            'type' => 'checkout.session.completed',
        ]);

        $response->assertStatus(400)
            ->assertSeeText('invalid');
    }
}
