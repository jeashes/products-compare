<?php

namespace Tests\Feature\Api\Products;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use App\Models\Category;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_product_successfully(): void
    {
        Storage::fake('public');
        $category = Category::factory()->create();

        $payload = [
            'category_id'    => $category->id,
            'name'           => 'Test Product',
            'image'          => 'https://example.test/img.webp',
            'price'          => 123.45,
            'rating'         => 4.5,
            'trending_order' => 1,
            'pros'           => ['Good'],
            'cons'           => ['Bad'],
            'key_features'   => ['Feature A'],
        ];

        $response = $this->postJson(route('products.store'), $payload);

        $response->assertCreated();
        $this->assertDatabaseHas('products', ['name' => 'Test Product']);
    }

    public function test_store_product_with_fail(): void
    {
        $category = Category::factory()->create();

        $payload = [
            'category_id'    => $category->id,
            'name'           => 'Invalid Product',
            'image'          => 'not-an-image',
            'price'          => 'abc',
            'rating'         => 6,
            'trending_order' => -1,
        ];

        $this->postJson(route('products.store'), $payload)->assertStatus(422);
    }
}
