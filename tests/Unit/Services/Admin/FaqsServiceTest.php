<?php

namespace Tests\Unit\Services\Admin;

use App\Models\Faq;
use App\Services\Admin\FaqsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class FaqsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_faq_with_a_generated_uid(): void
    {
        $service = app(FaqsService::class);

        $faq = $service->create([
            'question' => 'What is Classer?',
            'answer' => 'A home device and app.',
            'category' => 'Getting Started',
            'sort_order' => 1,
            'is_published' => true,
        ]);

        $this->assertNotEmpty($faq->uid);
        $this->assertDatabaseHas('faqs', ['question' => 'What is Classer?']);
    }

    public function test_it_toggles_published_state(): void
    {
        $faq = Faq::factory()->create(['is_published' => true]);
        $service = app(FaqsService::class);

        $service->setPublished($faq->uid, false);
        $this->assertFalse($faq->fresh()->is_published);

        $service->setPublished($faq->uid, true);
        $this->assertTrue($faq->fresh()->is_published);
    }

    public function test_it_filters_paginated_results_by_search_term(): void
    {
        Faq::factory()->create(['question' => 'Storage capacity question', 'category' => 'Storage']);
        Faq::factory()->create(['question' => 'Totally unrelated topic', 'category' => 'Support']);

        $service = app(FaqsService::class);
        $request = Request::create('/admin/faqs', 'GET', ['q' => 'Storage']);

        $result = $service->paginate($request);

        $this->assertSame(1, $result->total());
        $this->assertSame('Storage capacity question', $result->first()->question);
    }

    public function test_published_for_display_returns_frontend_shape(): void
    {
        Faq::factory()->create([
            'question' => 'Visible question',
            'answer' => 'Visible answer',
            'category' => 'Getting Started',
            'sort_order' => 0,
            'is_published' => true,
        ]);
        Faq::factory()->unpublished()->create(['question' => 'Hidden question']);

        $service = app(FaqsService::class);
        $display = $service->publishedForDisplay();

        $this->assertCount(1, $display);
        $this->assertSame(
            ['q' => 'Visible question', 'a' => 'Visible answer', 'category' => 'Getting Started'],
            $display->first()
        );
    }
}
