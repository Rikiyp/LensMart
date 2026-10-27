<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddressRequest;
use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AddressController extends Controller
{
    public function index(Request $request): View
    {
        $addresses = $request->user()->addresses()->orderByDesc('is_default')->latest()->get();

        return view('customer.addresses.index', compact('addresses'));
    }

    public function create(): View
    {
        return view('customer.addresses.form', ['address' => new Address]);
    }

    public function store(AddressRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        if ($request->boolean('is_default') || $request->user()->addresses()->count() === 0) {
            $request->user()->addresses()->update(['is_default' => false]);
            $data['is_default'] = true;
        }

        Address::create($data);

        return redirect()->route('customer.addresses.index')->with('success', 'Alamat berhasil ditambahkan.');
    }

    public function edit(Address $address): View
    {
        $this->authorize('update', $address);

        return view('customer.addresses.form', compact('address'));
    }

    public function update(AddressRequest $request, Address $address): RedirectResponse
    {
        $this->authorize('update', $address);

        $data = $request->validated();

        if ($request->boolean('is_default')) {
            $request->user()->addresses()->update(['is_default' => false]);
            $data['is_default'] = true;
        } elseif ($address->is_default) {
            $data['is_default'] = true;
        }

        $address->update($data);

        return redirect()->route('customer.addresses.index')->with('success', 'Alamat berhasil diperbarui.');
    }

    public function destroy(Request $request, Address $address): RedirectResponse
    {
        $this->authorize('delete', $address);

        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $request->user()->addresses()->orderBy('id')->first()?->update(['is_default' => true]);
        }

        return redirect()->route('customer.addresses.index')->with('success', 'Alamat berhasil dihapus.');
    }

    public function setDefault(Request $request, Address $address): RedirectResponse
    {
        $this->authorize('update', $address);

        $request->user()->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return redirect()->route('customer.addresses.index')->with('success', 'Alamat utama diperbarui.');
    }
}
