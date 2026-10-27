<?php

namespace App\Http\Controllers\Customer;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function create(Request $request, Order $order, Product $product): View
    {
        $this->ensureOrderIsReviewable($request, $order, $product);

        return view('customer.reviews.form', [
            'order' => $order,
            'product' => $product,
            'review' => new Review,
        ]);
    }

    public function store(ReviewRequest $request, Order $order, Product $product): RedirectResponse
    {
        $this->ensureOrderIsReviewable($request, $order, $product);

        $review = Review::create([
            'product_id' => $product->id,
            'customer_id' => $request->user()->id,
            'order_id' => $order->id,
            'rating' => $request->integer('rating'),
            'comment' => $request->input('comment'),
        ]);

        $this->storeImages($request, $review);

        return redirect()
            ->route('products.show', $product)
            ->with('success', 'Terima kasih! Ulasan Anda sudah dipublikasikan.');
    }

    public function edit(Request $request, Review $review): View
    {
        $this->authorize('update', $review);

        $review->load(['images', 'product', 'order']);

        return view('customer.reviews.form', [
            'order' => $review->order,
            'product' => $review->product,
            'review' => $review,
        ]);
    }

    public function update(ReviewRequest $request, Review $review): RedirectResponse
    {
        $this->authorize('update', $review);

        $review->update([
            'rating' => $request->integer('rating'),
            'comment' => $request->input('comment'),
        ]);

        $removeIds = collect($request->input('remove_images', []))->map(fn ($id) => (int) $id);
        $removeImages = $review->images()->whereIn('id', $removeIds)->get();

        foreach ($removeImages as $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }

        $this->storeImages($request, $review->refresh());

        return redirect()
            ->route('products.show', $review->product)
            ->with('success', 'Ulasan berhasil diperbarui.');
    }

    private function ensureOrderIsReviewable(Request $request, Order $order, Product $product): void
    {
        abort_unless($order->customer_id === $request->user()->id, 403);
        abort_unless($order->status === OrderStatus::Completed, 403);
        abort_unless($order->items()->where('product_id', $product->id)->exists(), 404);

        $alreadyReviewed = Review::where('product_id', $product->id)
            ->where('order_id', $order->id)
            ->exists();

        abort_if($alreadyReviewed, 403, 'Produk ini sudah Anda ulas untuk pesanan tersebut.');
    }

    private function storeImages(ReviewRequest $request, Review $review): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        $remaining = 3 - $review->images()->count();
        $files = array_slice($request->file('images'), 0, max(0, $remaining));

        foreach ($files as $file) {
            $review->images()->create([
                'path' => $file->store('review-images', 'public'),
            ]);
        }
    }
}
