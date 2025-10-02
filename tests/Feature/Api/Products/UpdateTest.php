<?php

namespace Tests\Feature\Api\Products;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Models\Category;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_product_update(): void
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

        Storage::fake('public');

        $payload = [
            'category_id'    => $category->id,
            'name'           => 'Updated Product',
            'image'          => 'https://example.test/img.png',
            'price'          => 55.55,
            'rating'         => 3.5,
            'trending_order' => 2,
            'pros'           => ['Updated'],
            'cons'           => [],
            'key_features'   => ['Updated Feature'],
        ];

        $this->putJson(route('products.update', $product->id), $payload)
             ->assertOk();

        $this->assertDatabaseHas('products', [
            'id'   => $product->id,
            'name' => 'Updated Product',
        ]);
    }

    public function test_fail_product_update(): void
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

        $payload = [
            'category_id' => $category->id,
            'image'       => 'not-image',
            'price'       => 'NaN',
            'rating'      => -10,
            'trending_order' => null,
        ];

        $this->putJson(route('products.update', $product->id), $payload)
             ->assertStatus(422);
    }
}
