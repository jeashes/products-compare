<?php

namespace Tests\Feature\Api\Products;

use Tests\TestCase;
use Illuminate\Support\Carbon;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Category;
use App\Models\Product;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_paginated_products(): void
    {
        Carbon::setTestNow('2025-01-01 12:00:00');

        $category = Category::factory()->create();
        Product::factory()->count(3)->for($category)->create([
            'image'          => 'https://example.test/img.webp',
            'price'          => 123.45,
            'rating'         => 4.5,
            'trending_order' => null,
            'pros'           => ['Good'],
            'cons'           => ['Bad'],
            'key_features'   => ['Feature A'],
        ]);

        $response = $this->getJson(route('products.index'));

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'category_id',
                    'name',
                    'image',
                    'price',
                    'rating',
                    'pros',
                    'cons',
                    'key_features',
                    'trending_order',
                    'created_at',
                    'updated_at',
                ],
            ],
            'links',
            'meta',
        ]);
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('data', 3)
                ->has('links')  
                ->has('meta')
                ->has('data.0', fn (AssertableJson $item) =>
                    $item->whereType('id', 'integer')
                        ->whereType('category_id', 'integer')
                        ->whereType('name', 'string')
                        ->whereType('image', 'string')
                        ->whereType('price', 'double')
                        ->whereType('rating', 'double')
                        ->whereType('pros', 'array|null')
                        ->whereType('cons', 'array|null')
                        ->whereType('key_features', 'array|null')
                        ->whereType('trending_order', 'integer|null')
                        ->whereType('created_at', 'string|null')
                        ->whereType('updated_at', 'string|null')
                        ->etc()
                )
        );
    }
}
