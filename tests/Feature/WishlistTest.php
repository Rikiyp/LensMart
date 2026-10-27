<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_card_heart_turns_red_after_a_product_is_wishlisted(): void
    {
        $customer = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($customer)
            ->get(route('products.index'))
            ->assertOk()
            ->assertSee('aria-label="Tambah '.$product->name.' ke wishlist"', false)
            ->assertSee('aria-pressed="false"', false)
            ->assertDontSee('aria-label="Hapus '.$product->name.' dari wishlist"', false);

        $this->actingAs($customer)
            ->post(route('customer.wishlist.store', $product))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $customer->id,
            'product_id' => $product->id,
        ]);

        $this->actingAs($customer)
            ->get(route('products.index'))
            ->assertOk()
            ->assertSee('aria-label="Hapus '.$product->name.' dari wishlist"', false)
            ->assertSee('aria-pressed="true"', false)
            ->assertSee('fill-rose-600', false)
            ->assertDontSee('aria-label="Tambah '.$product->name.' ke wishlist"', false);
    }

    public function test_removing_a_wishlist_entry_restores_the_outline_heart(): void
    {
        $customer = User::factory()->create();
        $product = Product::factory()->create();

        Wishlist::create([
            'user_id' => $customer->id,
            'product_id' => $product->id,
            'price_when_added' => $product->price,
        ]);

        $this->actingAs($customer)
            ->delete(route('customer.wishlist.destroy', $product))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $customer->id,
            'product_id' => $product->id,
        ]);

        $this->actingAs($customer)
            ->get(route('products.index'))
            ->assertOk()
            ->assertSee('aria-label="Tambah '.$product->name.' ke wishlist"', false)
            ->assertDontSee('aria-label="Hapus '.$product->name.' dari wishlist"', false);
    }
}
