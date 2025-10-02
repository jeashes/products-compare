<?php

namespace Tests\Feature\Api\Products;

use App\Models\Category;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_delete_product(): void
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

        $this->deleteJson(route('products.destroy', $product->id))
             ->assertOk();

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /** @test */
    public function test_delete_product_that_does_not_exist(): void
    {
        $this->deleteJson(route('products.destroy', 999999))
             ->assertNotFound();
    }
}
