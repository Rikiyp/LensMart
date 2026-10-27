<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SpecAttribute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompareController extends Controller
{
    public function index(Request $request): View
    {
        $ids = collect($request->session()->get('compare', []))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->take(3)
            ->values();

        $products = Product::query()
            ->publiclyVisible()
            ->whereIn('id', $ids)
            ->with(['primaryImage', 'brand', 'store', 'specs.specAttribute'])
            ->withRating()
            ->withCount(['reviews as reviews_count' => fn ($query) => $query->where('is_hidden', false)])
            ->get()
            ->sortBy(fn (Product $product) => $ids->search($product->id))
            ->values();

        if ($products->count() !== $ids->count()) {
            $request->session()->put('compare', $products->pluck('id')->all());
        }

        $attributeIds = $products->flatMap(fn (Product $product) => $product->specs->pluck('spec_attribute_id'))->unique();

        $attributes = SpecAttribute::query()
            ->whereIn('id', $attributeIds)
            ->with('category')
            ->orderBy('name')
            ->get();

        $specMatrix = $products->mapWithKeys(function (Product $product) {
            return [$product->id => $product->specs->pluck('value', 'spec_attribute_id')];
        });

        return view('compare.index', [
            'products' => $products,
            'attributes' => $attributes,
            'specMatrix' => $specMatrix,
        ]);
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->isPubliclyVisible(), 404);

        $ids = collect($request->session()->get('compare', []))->map(fn ($id) => (int) $id);

        if ($ids->contains($product->id)) {
            return back()->with('info', 'Produk sudah ada dalam perbandingan.');
        }

        if ($ids->count() >= 3) {
            return back()->with('error', 'Maksimal 3 produk dapat dibandingkan. Hapus salah satu terlebih dahulu.');
        }

        $ids->push($product->id);
        $request->session()->put('compare', $ids->all());

        return back()->with('success', "{$product->name} ditambahkan ke perbandingan.");
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $ids = collect($request->session()->get('compare', []))
            ->reject(fn ($id) => (int) $id === $product->id)
            ->values();

        $request->session()->put('compare', $ids->all());

        return back()->with('success', 'Produk dihapus dari perbandingan.');
    }

    public function clear(Request $request): RedirectResponse
    {
        $request->session()->forget('compare');

        return back()->with('success', 'Perbandingan dikosongkan.');
    }
}
