<?php

namespace App\Http\Controllers\Admin;

use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VerifyProductRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $verification = $request->query('verification');
        $search = $request->query('q');

        $products = Product::query()
            ->with(['store', 'category', 'brand', 'primaryImage'])
            ->when(
                in_array($verification, array_column(VerificationStatus::cases(), 'value'), true),
                fn ($query) => $query->where('verification_status', $verification)
            )
            ->when($search, fn ($query, $keyword) => $query->where('name', 'like', "%{$keyword}%"))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'verification' => $verification,
            'search' => $search,
            'verifications' => VerificationStatus::cases(),
        ]);
    }

    public function verify(VerifyProductRequest $request, Product $product): RedirectResponse
    {
        if ($request->input('action') === 'approve') {
            $product->update([
                'verification_status' => VerificationStatus::Approved,
                'rejection_note' => null,
            ]);

            return back()->with('success', "Produk {$product->name} disetujui dan tampil di katalog.");
        }

        $product->update([
            'verification_status' => VerificationStatus::Rejected,
            'rejection_note' => $request->input('rejection_note'),
        ]);

        return back()->with('success', "Produk {$product->name} ditolak.");
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return back()->with('success', 'Produk dihapus dari katalog.');
    }
}
