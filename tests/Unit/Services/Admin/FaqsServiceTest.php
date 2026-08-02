<?php

namespace Tests\Unit\Services\Admin;

use App\Services\Admin\FaqsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FaqsServiceTest extends TestCase
{
    public function test_it_creates_a_faq_with_a_generated_uid(): void
    {
        Storage::fake('local');

        $service = app(FaqsService::class);

        $faq = $service->create([
            'question' => 'What is Classer?',
            'answer' => 'A home device and app.',
            'category' => 'Getting Started',
            'sort_order' => 1,
            'is_published' => true,
        ]);

        $this->assertNotEmpty($faq->uid);

        $stored = json_decode((string) Storage::disk('local')->get('public/faqs.json'), true);

        $this->assertSame('What is Classer?', $stored[0]['question']);
    }

    public function test_it_toggles_published_state(): void
    {
        Storage::fake('local');
        $service = app(FaqsService::class);

        $faq = $service->create([
            'question' => 'Visible question',
            'answer' => 'Visible answer',
            'category' => 'Getting Started',
            'sort_order' => 0,
            'is_published' => true,
        ]);

        $service->setPublished($faq->uid, false);
        $this->assertFalse($service->getByUid($faq->uid)?->is_published);

        $service->setPublished($faq->uid, true);
        $this->assertTrue($service->getByUid($faq->uid)?->is_published);
    }

    public function test_it_filters_paginated_results_by_search_term(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('public/faqs.json', json_encode([
            [
                'uid' => 'storage-question',
                'question' => 'Storage capacity question',
                'answer' => 'Storage answer',
                'category' => 'Storage',
                'sort_order' => 0,
                'is_published' => true,
                'created_at' => now()->subDay()->toIso8601String(),
                'updated_at' => now()->subDay()->toIso8601String(),
            ],
            [
                'uid' => 'support-question',
                'question' => 'Totally unrelated topic',
                'answer' => 'Support answer',
                'category' => 'Support',
                'sort_order' => 1,
                'is_published' => true,
                'created_at' => now()->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $service = app(FaqsService::class);
        $request = Request::create('/admin/faqs', 'GET', ['q' => 'Storage']);

        $result = $service->paginate($request);

        $this->assertSame(1, $result->total());
        $this->assertSame('Storage capacity question', $result->first()->question);
    }

    public function test_published_for_display_returns_frontend_shape(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('public/faqs.json', json_encode([
            [
                'uid' => 'visible-question',
                'question' => 'Visible question',
                'answer' => 'Visible answer',
                'category' => 'Getting Started',
                'sort_order' => 0,
                'is_published' => true,
                'created_at' => now()->subHour()->toIso8601String(),
                'updated_at' => now()->subHour()->toIso8601String(),
            ],
            [
                'uid' => 'hidden-question',
                'question' => 'Hidden question',
                'answer' => 'Hidden answer',
                'category' => 'Support',
                'sort_order' => 1,
                'is_published' => false,
                'created_at' => now()->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $service = app(FaqsService::class);
        $display = $service->publishedForDisplay();

        $this->assertCount(1, $display);
        $this->assertSame(
            ['q' => 'Visible question', 'a' => 'Visible answer', 'category' => 'Getting Started'],
            $display->first()
        );
    }
}
