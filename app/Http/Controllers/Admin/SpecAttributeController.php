<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SpecAttributeRequest;
use App\Models\Category;
use App\Models\SpecAttribute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SpecAttributeController extends Controller
{
    public function index(Request $request): View
    {
        $specAttributes = SpecAttribute::with('category')->withCount('products')->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $editing = $request->filled('edit') ? SpecAttribute::find($request->query('edit')) : null;

        return view('admin.master.spec-attributes', compact('specAttributes', 'categories', 'editing'));
    }

    public function store(SpecAttributeRequest $request): RedirectResponse
    {
        SpecAttribute::create($request->validated());

        return back()->with('success', 'Atribut spesifikasi ditambahkan.');
    }

    public function update(SpecAttributeRequest $request, SpecAttribute $specAttribute): RedirectResponse
    {
        $specAttribute->update($request->validated());

        return redirect()->route('admin.spec-attributes.index')->with('success', 'Atribut spesifikasi diperbarui.');
    }

    public function destroy(SpecAttribute $specAttribute): RedirectResponse
    {
        if ($specAttribute->products()->exists()) {
            return back()->with('error', 'Atribut masih digunakan oleh produk.');
        }

        $specAttribute->delete();

        return back()->with('success', 'Atribut dihapus.');
    }
}
