<?php

namespace Tests\Unit\Favorite;

use App\Models\Category;
use App\Models\Favorite;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\Favorite\FavoriteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteServiceTest extends TestCase
{
    use RefreshDatabase;

    private FavoriteService $service;
    private User $user;
    private Product $product;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new FavoriteService();

        $this->user = User::factory()->create();

        $this->category = Category::factory()->create();

        $store = Store::factory()->create([
            'category_id' => $this->category->id,
        ]);

        $this->product = Product::factory()->create([
            'store_id' => $store->id,
        ]);
    }

    public function test_it_returns_user_favorites_with_relationships()
    {
        Favorite::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'category_id' => $this->product->store->category->id,
        ]);

        $favorites = $this->service->getUserFavorites($this->user->id);

        $this->assertCount(1, $favorites);

        $this->assertTrue(
            $favorites->first()->relationLoaded('product')
        );

        $this->assertTrue(
            $favorites->first()->product->relationLoaded('store')
        );
    }

    public function test_it_adds_product_to_favorites_successfully()
    {
        $favorite = $this->service->addToFavorites(
            $this->user->id,
            $this->product->id
        );

        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'category_id' => $this->product->store->category->id,
        ]);

        $this->assertNotNull($favorite);
    }

    public function test_it_throws_exception_when_product_already_in_favorites()
    {
        Favorite::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'category_id' => $this->category->id,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This product is already in your favorites.');

        $this->service->addToFavorites(
            $this->user->id,
            $this->product->id
        );
    }

    public function test_it_removes_product_from_favorites()
    {
        Favorite::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'category_id' => $this->category->id,
        ]);

        $result = $this->service->removeFromFavorites(
            $this->user->id,
            $this->product->id
        );

        $this->assertTrue($result);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);
    }

    public function test_it_throws_exception_when_removing_non_existing_favorite()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This product is not in your favorites.');

        $this->service->removeFromFavorites(
            $this->user->id,
            $this->product->id
        );
    }
}
