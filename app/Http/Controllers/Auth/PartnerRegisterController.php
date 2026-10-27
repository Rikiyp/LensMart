<?php

namespace App\Http\Controllers\Auth;

use App\Enums\StoreStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PartnerRegisterRequest;
use App\Models\Store;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PartnerRegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.partner-register');
    }

    public function store(PartnerRegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $legalDocument = $request->hasFile('legal_document')
            ? $request->file('legal_document')->store('legal-documents', 'public')
            : null;

        $user = DB::transaction(function () use ($data, $legalDocument) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'partner',
                'phone' => $data['phone'],
                'is_active' => true,
            ]);

            Store::create([
                'user_id' => $user->id,
                'name' => $data['store_name'],
                'slug' => Store::generateSlug($data['store_name']),
                'description' => $data['description'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'],
                'phone' => $data['phone'],
                'legal_document' => $legalDocument,
                'status' => StoreStatus::Pending,
            ]);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect()
            ->route('partner.dashboard')
            ->with('success', 'Pendaftaran toko berhasil. Lengkapi profil dan tunggu verifikasi admin.');
    }
}
