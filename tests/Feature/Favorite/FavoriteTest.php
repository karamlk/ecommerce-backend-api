<?php

namespace Tests\Feature\Favorite;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Store;
use App\Models\Category;
use App\Models\Favorite;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    protected function createProductWithRelations()
    {
        $category = Category::factory()->create();

        $store = Store::factory()->create([
            'category_id' => $category->id,
        ]);

        $product = Product::factory()->create([
            'store_id' => $store->id,
        ]);

        return [$product, $category];
    }

    public function test_user_can_get_favorites_grouped_by_category()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        [$product, $category] = $this->createProductWithRelations();

        Favorite::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'category_id' => $category->id,
        ]);

        $response = $this->getJson('/api/favorites');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'categories' => [
                        [
                            'category_name',
                            'favorites'
                        ]
                    ]
                ]
            ]);
    }

    public function test_user_can_add_product_to_favorites()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        [$product, $category] = $this->createProductWithRelations();

        $response = $this->postJson('/api/favorites', [
            'product_id' => $product->id,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Product added to favorites successfully.'
            ]);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'product_id' => $product->id,
            'category_id' => $category->id,
        ]);
    }

    public function test_user_cannot_add_duplicate_favorite()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        [$product, $category] = $this->createProductWithRelations();

        Favorite::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'category_id' => $category->id,
        ]);

        $response = $this->postJson('/api/favorites', [
            'product_id' => $product->id,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'This product is already in your favorites.'
            ]);
    }

    public function test_user_can_remove_favorite()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        [$product, $category] = $this->createProductWithRelations();

        Favorite::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'category_id' => $category->id,
        ]);

        $response = $this->deleteJson('/api/favorites', [
            'product_id' => $product->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Product removed from favorites successfully.'
            ]);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_existing_favorite_returns_404()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        [$product] = $this->createProductWithRelations();

        $response = $this->deleteJson('/api/favorites', [
            'product_id' => $product->id,
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'This product is not in your favorites.'
            ]);
    }

    public function test_guest_cannot_access_favorites()
    {
        $response = $this->getJson('/api/favorites');

        $response->assertStatus(401);
    }
}