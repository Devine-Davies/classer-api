<?php

namespace Tests\Feature;

use App\Services\Admin\TutorialsItemsService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TutorialsItemsServiceTest extends TestCase
{
    public function test_tutorial_items_can_be_loaded_from_json_file_and_saved_back(): void
    {
        Storage::fake('local');

        $sampleItems = [[
            'id' => 'importing-videos',
            'label' => 'Importing videos',
            'url' => 'https://www.youtube.com/watch?v=xahA3lZR3Ew',
            'thumbnail' => 'https://example.com/importing.png',
            'description' => 'Learn how to bring action camera recordings into Classer.',
            'alt' => 'Importing videos tutorial preview',
            'sortOrder' => 1,
        ]];

        Storage::disk('local')->put('public/tutorials-items.json', json_encode($sampleItems, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $service = new TutorialsItemsService;
        $items = $service->getItems();

        $this->assertCount(1, $items);
        $this->assertSame('importing-videos', $items[0]['id']);

        $service->saveItems(array_merge($items, [[
            'id' => 'tagging',
            'label' => 'Create and search tags',
            'url' => 'https://www.youtube.com/watch?v=FiSCAcEcodU',
            'thumbnail' => 'https://example.com/tags.png',
            'description' => 'Learn how to create and search tags.',
            'alt' => 'Tagging tutorial preview',
            'sortOrder' => 2,
        ]]));

        $savedItems = $service->getItems();

        $this->assertCount(2, $savedItems);
        $this->assertSame('tagging', $savedItems[1]['id']);
    }
}
