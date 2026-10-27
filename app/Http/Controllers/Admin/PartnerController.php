<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StoreStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VerifyStoreRequest;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PartnerController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $stores = Store::query()
            ->with('user')
            ->withCount('products')
            ->when(in_array($status, array_column(StoreStatus::cases(), 'value'), true), fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.partners.index', [
            'stores' => $stores,
            'status' => $status,
            'statuses' => StoreStatus::cases(),
        ]);
    }

    public function show(Store $store): View
    {
        $store->load(['user', 'products' => fn ($query) => $query->with('primaryImage')->latest(), 'payouts'])
            ->loadCount('orders');

        return view('admin.partners.show', compact('store'));
    }

    public function verify(VerifyStoreRequest $request, Store $store): RedirectResponse
    {
        if ($request->input('action') === 'verify') {
            $store->update([
                'status' => StoreStatus::Verified,
                'rejection_note' => null,
            ]);

            return back()->with('success', "Toko {$store->name} berhasil diverifikasi.");
        }

        $store->update([
            'status' => StoreStatus::Rejected,
            'rejection_note' => $request->input('rejection_note'),
        ]);

        return back()->with('success', "Toko {$store->name} ditolak.");
    }
}
