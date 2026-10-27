<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReviewReportStatus;
use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\ReviewReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $visibility = $request->query('visibility', 'all');

        $reviews = Review::query()
            ->with(['product.store', 'customer', 'images', 'order', 'reports.store'])
            ->when($visibility === 'hidden', fn ($query) => $query->where('is_hidden', true))
            ->when($visibility === 'visible', fn ($query) => $query->where('is_hidden', false))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $reports = ReviewReport::query()
            ->with(['review.product', 'store'])
            ->latest()
            ->paginate(10, ['*'], 'reports_page');

        return view('admin.reviews.index', [
            'reviews' => $reviews,
            'reports' => $reports,
            'visibility' => $visibility,
        ]);
    }

    public function toggle(Review $review): RedirectResponse
    {
        $review->update(['is_hidden' => ! $review->is_hidden]);

        return back()->with('success', $review->is_hidden
            ? 'Ulasan disembunyikan dari publik.'
            : 'Ulasan ditampilkan kembali.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        foreach ($review->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        $review->delete();

        return back()->with('success', 'Ulasan dihapus permanen.');
    }

    public function respond(Request $request, ReviewReport $report): RedirectResponse
    {
        $validated = $request->validate([
            'admin_response' => ['required', 'string', 'max:2000'],
        ]);

        $report->update([
            'admin_response' => $validated['admin_response'],
            'status' => ReviewReportStatus::Resolved,
        ]);

        return back()->with('success', 'Laporan ulasan ditanggapi.');
    }
}
