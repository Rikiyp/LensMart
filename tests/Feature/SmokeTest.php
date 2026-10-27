<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\VerificationStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_pages_render_without_errors(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@lensmart.test')->firstOrFail();
        $partner = User::where('email', 'partner1@lensmart.test')->firstOrFail();
        $customer = User::where('email', 'customer@lensmart.test')->firstOrFail();

        $product = Product::where('verification_status', VerificationStatus::Approved)
            ->where('status', 'active')
            ->firstOrFail();

        $partnerProduct = Product::where('store_id', $partner->store->id)->firstOrFail();
        $partnerOrder = Order::where('store_id', $partner->store->id)->firstOrFail();
        $customerOrder = Order::where('customer_id', $customer->id)->firstOrFail();
        $payment = Payment::where('customer_id', $customer->id)
            ->where('status', PaymentStatus::Unpaid)
            ->firstOrFail();
        $customerReview = Review::where('customer_id', $customer->id)->firstOrFail();
        $customerAddress = $customer->addresses()->firstOrFail();

        $public = [
            route('home'),
            route('products.index'),
            route('products.index', ['category' => $product->category_id, 'sort' => 'termurah']),
            route('products.show', $product),
            route('compare.index'),
            route('login'),
            route('register'),
            route('partner.register'),
        ];

        foreach ($public as $url) {
            $this->get($url)->assertStatus(200, $url);
        }

        $customerPages = [
            route('customer.dashboard'),
            route('customer.cart.index'),
            route('customer.checkout.index'),
            route('customer.orders.index'),
            route('customer.orders.show', $customerOrder),
            route('customer.payments.show', $payment),
            route('customer.wishlist.index'),
            route('customer.addresses.index'),
            route('customer.addresses.create'),
            route('customer.addresses.edit', $customerAddress),
            route('notifications.index'),
            route('profile.edit'),
            route('customer.reviews.edit', $customerReview),
        ];

        foreach ($customerPages as $url) {
            $this->actingAs($customer)->get($url)->assertStatus(200, $url);
        }

        $this->actingAs($partner);
        $this->assertTrue(
            \Illuminate\Support\Facades\Gate::forUser($partner)->allows('update', $partnerProduct),
            'policy update false for product '.$partnerProduct->id.' store '.$partnerProduct->store_id.' user store '.$partner->store->id
        );

        $partnerPages = [
            route('partner.dashboard'),
            route('partner.store.edit'),
            route('partner.products.index'),
            route('partner.products.create'),
            route('partner.products.edit', $partnerProduct),
            route('partner.orders.index'),
            route('partner.orders.show', $partnerOrder),
            route('partner.finance.index'),
            route('partner.reviews.index'),
        ];

        foreach ($partnerPages as $url) {
            $response = $this->actingAs($partner)->get($url);

            if ($response->status() !== 200) {
                $this->fail($url.' -> '.$response->status().' :: '.mb_substr(strip_tags($response->getContent()), 0, 300));
            }
        }

        $adminPages = [
            route('admin.dashboard'),
            route('admin.users.index'),
            route('admin.partners.index'),
            route('admin.partners.show', $partner->store),
            route('admin.products.index'),
            route('admin.orders.index'),
            route('admin.orders.show', $customerOrder),
            route('admin.reports.index'),
            route('admin.reviews.index'),
            route('admin.categories.index'),
            route('admin.brands.index'),
            route('admin.spec-attributes.index'),
            route('admin.payouts.index'),
        ];

        foreach ($adminPages as $url) {
            $this->actingAs($admin)->get($url)->assertStatus(200, $url);
        }

        $this->actingAs($admin)->get(route('admin.reports.pdf'))->assertOk();
        $this->actingAs($admin)->get(route('admin.reports.excel'))->assertOk();
    }
}
