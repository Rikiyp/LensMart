<?php

namespace App\Http\Controllers\Partner;

use App\Enums\StoreStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function edit(Request $request): View
    {
        $store = $request->user()->store;

        abort_unless($store, 403);

        return view('partner.store.edit', compact('store'));
    }

    public function update(StoreProfileRequest $request): RedirectResponse
    {
        $store = $request->user()->store;

        abort_unless($store, 403);

        $data = $request->validated();

        if ($request->hasFile('logo')) {
            if ($store->logo) {
                Storage::disk('public')->delete($store->logo);
            }
            $data['logo'] = $request->file('logo')->store('store-logos', 'public');
        }

        if ($request->hasFile('legal_document')) {
            if ($store->legal_document) {
                Storage::disk('public')->delete($store->legal_document);
            }
            $data['legal_document'] = $request->file('legal_document')->store('legal-documents', 'public');
        }

        if ($store->status === StoreStatus::Rejected && $request->boolean('resubmit')) {
            $data['status'] = StoreStatus::Pending;
            $data['rejection_note'] = null;
        }

        $store->update($data);

        return back()->with('success', 'Profil toko berhasil diperbarui.');
    }
}
