<?php

namespace App\Http\Controllers\Partner;

use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCompatibility;
use App\Models\ProductImage;
use App\Models\SpecAttribute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $store = $request->user()->store;

        $products = Product::query()
            ->where('store_id', $store->id)
            ->when($request->query('q'), fn ($query, $keyword) => $query->where('name', 'like', "%{$keyword}%"))
            ->when($request->query('verification'), fn ($query, $status) => $query->where('verification_status', $status))
            ->with(['primaryImage', 'category', 'brand'])
            ->withCount('reviews')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('partner.products.index', [
            'products' => $products,
            'verifications' => VerificationStatus::cases(),
        ]);
    }

    public function create(): View
    {
        return view('partner.products.form', [
            'product' => new Product,
            'categories' => Category::orderBy('name')->get(),
            'brands' => Brand::orderBy('name')->get(),
            'specAttributes' => SpecAttribute::with('category')->orderBy('name')->get(),
            'specValues' => collect(),
            'compatibilities' => collect(),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        // Validasi jumlah foto sebelum transaksi DB (konsisten dengan update()).
        $newImages = count($request->file('images', []));
        if ($newImages > 5) {
            return back()->withInput()->withErrors(['images' => 'Total foto produk maksimal 5.']);
        }

        $store = $request->user()->store;

        $product = DB::transaction(function () use ($request, $store) {
            $product = Product::create([
                ...$this->productAttributes($request),
                'store_id' => $store->id,
                'slug' => Product::generateSlug($request->string('name')->toString()),
                'verification_status' => VerificationStatus::Pending,
            ]);

            $this->syncSpecs($product, $request);
            $this->syncCompatibilities($product, $request);
            $this->storeImages($product, $request);

            return $product;
        });

        return redirect()
            ->route('partner.products.edit', $product)
            ->with('success', 'Produk dibuat dan menunggu verifikasi admin.');
    }

    public function edit(Request $request, Product $product): View
    {
        $this->authorize('update', $product);

        $product->load(['images', 'specs', 'compatibilities']);

        return view('partner.products.form', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
            'brands' => Brand::orderBy('name')->get(),
            'specAttributes' => SpecAttribute::with('category')->orderBy('name')->get(),
            'specValues' => $product->specs->pluck('value', 'spec_attribute_id'),
            'compatibilities' => $product->compatibilities,
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $existingImages = $product->images()->count();
        $removedImages = count($request->input('remove_images', []));
        $newImages = count($request->file('images', []));

        if ($existingImages - $removedImages + $newImages > 5) {
            return back()->withInput()->withErrors(['images' => 'Total foto produk maksimal 5.']);
        }

        DB::transaction(function () use ($request, $product) {
            $data = $this->productAttributes($request);

            if ($product->name !== $request->string('name')->toString()) {
                $data['slug'] = Product::generateSlug($request->string('name')->toString(), $product->id);
            }

            if ($product->verification_status === VerificationStatus::Rejected) {
                $data['verification_status'] = VerificationStatus::Pending;
                $data['rejection_note'] = null;
            }

            $product->update($data);

            $this->syncSpecs($product, $request);
            $this->syncCompatibilities($product, $request);
            $this->removeImages($product, $request);
            $this->storeImages($product, $request);
            $this->setPrimaryImage($product, $request);
        });

        return redirect()
            ->route('partner.products.edit', $product)
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $product->delete();

        return redirect()->route('partner.products.index')->with('success', 'Produk dihapus.');
    }

    private function productAttributes(ProductRequest $request): array
    {
        return [
            'category_id' => $request->integer('category_id'),
            'brand_id' => $request->integer('brand_id'),
            'name' => $request->string('name')->toString(),
            'model_name' => $request->string('model_name')->toString(),
            'description' => $request->input('description'),
            'price' => $request->integer('price'),
            'stock' => $request->integer('stock'),
            'condition' => $request->input('condition'),
            'status' => $request->input('status'),
        ];
    }

    private function syncSpecs(Product $product, ProductRequest $request): void
    {
        $specs = $request->input('specs', []);
        $validAttributeIds = SpecAttribute::whereIn('id', array_keys($specs))->pluck('id');

        $product->specs()->delete();

        foreach ($specs as $attributeId => $value) {
            if (blank($value) || ! $validAttributeIds->contains((int) $attributeId)) {
                continue;
            }

            $product->specs()->create([
                'spec_attribute_id' => (int) $attributeId,
                'value' => trim((string) $value),
            ]);
        }
    }

    private function syncCompatibilities(Product $product, ProductRequest $request): void
    {
        $product->compatibilities()->delete();

        foreach ($request->input('compatibilities', []) as $compatibility) {
            if (empty($compatibility['brand_id']) || empty($compatibility['model_name'])) {
                continue;
            }

            $product->compatibilities()->create([
                'brand_id' => (int) $compatibility['brand_id'],
                'model_name' => trim((string) $compatibility['model_name']),
            ]);
        }
    }

    private function storeImages(Product $product, ProductRequest $request): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        $hasPrimary = $product->images()->where('is_primary', true)->exists();
        $sortOrder = (int) $product->images()->max('sort_order');

        foreach ($request->file('images') as $index => $file) {
            $product->images()->create([
                'path' => $file->store('products', 'public'),
                'is_primary' => ! $hasPrimary && $index === 0,
                'sort_order' => ++$sortOrder,
            ]);
            $hasPrimary = true;
        }
    }

    private function removeImages(Product $product, ProductRequest $request): void
    {
        $removeIds = collect($request->input('remove_images', []))->map(fn ($id) => (int) $id);

        $images = $product->images()->whereIn('id', $removeIds)->get();

        foreach ($images as $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }

        if (! $product->images()->where('is_primary', true)->exists()) {
            $product->images()->orderBy('sort_order')->first()?->update(['is_primary' => true]);
        }
    }

    private function setPrimaryImage(Product $product, ProductRequest $request): void
    {
        $primaryId = $request->integer('primary_image_id');

        if (! $primaryId) {
            return;
        }

        $image = $product->images()->whereKey($primaryId)->first();

        if (! $image) {
            return;
        }

        $product->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);
    }
}
