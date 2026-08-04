<?php

namespace Tests\Feature;

use App\Services\Admin\ShippingService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShippingServiceTest extends TestCase
{
    public function test_shipping_rows_can_be_loaded_and_updated(): void
    {
        Storage::fake('local');

        $sampleShippingRows = [
            [
                'countryCode' => 'GBR',
                'rmCountryCode' => 'GB',
                'displayName' => 'United Kingdom',
                'postageZone' => 'UK',
                'is_published' => true,
                'shipping' => [
                    '1' => [
                        [
                            'method' => 'Standard',
                            'cost' => 150,
                            'weightLimit' => 500,
                        ],
                    ],
                ],
            ],
            [
                'countryCode' => 'FRA',
                'rmCountryCode' => 'FR',
                'displayName' => 'France',
                'postageZone' => 'EuropeanUnion',
                'is_published' => true,
                'shipping' => [
                    '1' => [
                        [
                            'method' => 'Standard',
                            'cost' => 300,
                            'weightLimit' => 1000,
                        ],
                    ],
                ],
            ],
        ];

        Storage::disk('local')->put('public/shipping.json', json_encode($sampleShippingRows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $service = new ShippingService;
        $items = $service->getItems();

        $this->assertCount(2, $items);
        $this->assertSame(0, $items[0]['_row']);
        $this->assertSame('United Kingdom', $items[0]['displayName']);

        $updated = $service->updateByRow(1, [
            'countryCode' => 'FRA',
            'rmCountryCode' => 'FR',
            'displayName' => 'France - Mainland',
            'postageZone' => 'EuropeanUnion',
            'is_published' => 0,
            'postcodeRegex' => '^\\d{5}$',
            'shipping_rates' => [[
                'vendor_id' => '1',
                'vendor_title' => 'Royal Mail',
                'method' => 'Standard',
                'cost' => 450,
                'weight_limit' => 1200,
            ]],
        ]);

        $this->assertTrue($updated);

        $saved = $service->getItems();

        $this->assertSame('France - Mainland', $saved[1]['displayName']);
        $this->assertSame(450, $saved[1]['_shipping_cost']);
        $this->assertSame(1200, $saved[1]['_shipping_weight_limit']);
        $this->assertSame('^\\d{5}$', $saved[1]['postcodeRegex']);
        $this->assertFalse($saved[1]['_is_published']);

        $createdRow = $service->create([
            'countryCode' => 'USA',
            'rmCountryCode' => 'US',
            'displayName' => 'United States - Mainland',
            'postageZone' => 'World Zone 3',
            'is_published' => 1,
            'postcodeRegex' => '^\\d{5}(-\\d{4})?$',
            'shipping_rates' => [[
                'vendor_id' => '1',
                'vendor_title' => 'USPS',
                'method' => 'Standard',
                'cost' => 650,
                'weight_limit' => 1500,
            ]],
        ]);

        $this->assertSame(2, $createdRow);

        $created = $service->getByRow($createdRow);

        $this->assertNotNull($created);
        $this->assertSame('US', $created['rmCountryCode']);
        $this->assertSame(650, $created['_shipping_cost']);
        $this->assertTrue($created['_is_published']);
        $this->assertSame('USPS', $created['_shipping_rates'][0]['vendor_title']);

        $deleted = $service->deleteByRow(0);

        $this->assertTrue($deleted);

        $afterDelete = $service->getItems();

        $this->assertCount(2, $afterDelete);
        $this->assertSame('France - Mainland', $afterDelete[0]['displayName']);
        $this->assertSame('United States - Mainland', $afterDelete[1]['displayName']);
    }
}
