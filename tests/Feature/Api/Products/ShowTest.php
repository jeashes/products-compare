<?php

namespace Tests\Feature\Api\Products;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;
use App\Models\Category;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_product_successfully(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create([
            'image'          => 'https://example.test/img.webp',
            'price'          => 123.45,
            'rating'         => 4.5,
            'trending_order' => null,
            'pros'           => ['Good'],
            'cons'           => ['Bad'],
            'key_features'   => ['Feature A'],
        ]);

        $response = $this->getJson(route('products.show', $product->id));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
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
            ]);
    }

    public function test_show_product_that_does_not_exist(): void
    {
        $this->getJson(route('products.show', 999999))
             ->assertNotFound();
    }
}
