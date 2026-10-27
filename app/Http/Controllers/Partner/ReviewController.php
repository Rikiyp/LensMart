<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\ReviewReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $store = $request->user()->store;

        $reviews = Review::query()
            ->whereHas('product', fn ($query) => $query->where('store_id', $store->id))
            ->with(['product.primaryImage', 'customer', 'images', 'order', 'reports' => fn ($query) => $query->where('store_id', $store->id)])
            ->latest()
            ->paginate(10);

        return view('partner.reviews.index', compact('reviews', 'store'));
    }

    public function reply(Request $request, Review $review): RedirectResponse
    {
        $this->ensureReviewBelongsToStore($request, $review);

        $validated = $request->validate([
            'store_reply' => ['required', 'string', 'max:2000'],
        ]);

        if ($review->store_reply) {
            return back()->with('error', 'Ulasan ini sudah dibalas.');
        }

        $review->update([
            'store_reply' => $validated['store_reply'],
            'replied_at' => now(),
        ]);

        return back()->with('success', 'Balasan ulasan terkirim.');
    }

    public function report(Request $request, Review $review): RedirectResponse
    {
        $this->ensureReviewBelongsToStore($request, $review);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        ReviewReport::firstOrCreate(
            [
                'review_id' => $review->id,
                'store_id' => $request->user()->store->id,
            ],
            [
                'reason' => $validated['reason'],
                'status' => 'open',
            ]
        );

        return back()->with('success', 'Ulasan dilaporkan ke admin.');
    }

    private function ensureReviewBelongsToStore(Request $request, Review $review): void
    {
        $store = $request->user()->store;

        abort_unless(
            $review->product && $review->product->store_id === $store?->id,
            403
        );
    }
}
