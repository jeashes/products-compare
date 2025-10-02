<?php

namespace Tests\Feature\Api\Products;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;
use App\Models\Category;

class Top10Test extends TestCase
{
    use RefreshDatabase;

    public function test_get_top10_products(): void
    {
        $category = Category::factory()->create();

        Product::factory()
            ->count(1)
            ->for($category)
            ->sequence(fn ($seq) => ['trending_order' => $seq->index])
            ->create([
                'image'          => 'https://example.test/img.webp',
                'price'          => 123.45,
                'rating'         => 4.5,
                'trending_order' => 1,
                'pros'           => ['Good'],
                'cons'           => ['Bad'],
                'key_features'   => ['Feature A'],
            ]);

        $response = $this->getJson(route('products.top10'));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonStructure([
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
            ]);

        $orders = collect($response->json('data'))->pluck('trending_order');
        $this->assertEquals($orders->sort()->values()->all(), $orders->all());
    }
}
