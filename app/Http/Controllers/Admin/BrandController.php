<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BrandRequest;
use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function index(Request $request): View
    {
        $brands = Brand::withCount('products')->orderBy('name')->get();
        $editing = $request->filled('edit') ? Brand::find($request->query('edit')) : null;

        return view('admin.master.brands', compact('brands', 'editing'));
    }

    public function store(BrandRequest $request): RedirectResponse
    {
        Brand::create([
            'name' => $request->string('name')->toString(),
            'slug' => $this->uniqueSlug($request->string('name')->toString()),
        ]);

        return back()->with('success', 'Merek ditambahkan.');
    }

    public function update(BrandRequest $request, Brand $brand): RedirectResponse
    {
        $name = $request->string('name')->toString();

        $brand->update([
            'name' => $name,
            'slug' => $brand->name === $name ? $brand->slug : $this->uniqueSlug($name, $brand->id),
        ]);

        return redirect()->route('admin.brands.index')->with('success', 'Merek diperbarui.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        if ($brand->products()->exists() || $brand->compatibilities()->exists()) {
            return back()->with('error', 'Merek masih digunakan oleh produk atau kompatibilitas.');
        }

        $brand->delete();

        return back()->with('success', 'Merek dihapus.');
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;

        while (Brand::where('slug', $slug)->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.(++$counter);
        }

        return $slug;
    }
}
