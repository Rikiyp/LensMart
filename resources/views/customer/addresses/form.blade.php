<x-account-shell :title="$address->exists ? 'Edit Alamat' : 'Tambah Alamat'" subtitle="Lengkapi detail alamat pengiriman Anda.">
    <div class="mb-5">
        <a href="{{ route('customer.addresses.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-ink-500 hover:text-amber-600">
            <x-icon name="arrow-left" class="h-3.5 w-3.5" /> Kembali ke daftar alamat
        </a>
    </div>

    <form method="POST" action="{{ $address->exists ? route('customer.addresses.update', $address) : route('customer.addresses.store') }}"
          class="card max-w-2xl p-5 sm:p-6">
        @csrf
        @if ($address->exists)
            @method('PUT')
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="label" class="label">Label alamat</label>
                <input id="label" type="text" name="label" value="{{ old('label', $address->label) }}" required maxlength="50" class="input" placeholder="Contoh: Rumah, Kantor">
                <x-input-error :messages="$errors->get('label')" class="mt-2" />
            </div>

            <div>
                <label for="recipient_name" class="label">Nama penerima</label>
                <input id="recipient_name" type="text" name="recipient_name" value="{{ old('recipient_name', $address->recipient_name) }}" required maxlength="255" class="input" placeholder="Nama lengkap penerima">
                <x-input-error :messages="$errors->get('recipient_name')" class="mt-2" />
            </div>

            <div>
                <label for="phone" class="label">Nomor telepon</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone', $address->phone) }}" required maxlength="30" class="input" placeholder="08xxxxxxxxxx">
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>

            <div class="sm:col-span-2">
                <label for="address_line" class="label">Alamat lengkap</label>
                <textarea id="address_line" name="address_line" rows="3" required maxlength="500" class="textarea" placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan">{{ old('address_line', $address->address_line) }}</textarea>
                <x-input-error :messages="$errors->get('address_line')" class="mt-2" />
            </div>

            <div>
                <label for="city" class="label">Kota / Kabupaten</label>
                <input id="city" type="text" name="city" value="{{ old('city', $address->city) }}" required maxlength="100" class="input" placeholder="Contoh: Jakarta Selatan">
                <x-input-error :messages="$errors->get('city')" class="mt-2" />
            </div>

            <div>
                <label for="province" class="label">Provinsi</label>
                <input id="province" type="text" name="province" value="{{ old('province', $address->province) }}" required maxlength="100" class="input" placeholder="Contoh: DKI Jakarta">
                <x-input-error :messages="$errors->get('province')" class="mt-2" />
            </div>

            <div>
                <label for="postal_code" class="label">Kode pos</label>
                <input id="postal_code" type="text" name="postal_code" value="{{ old('postal_code', $address->postal_code) }}" required maxlength="10" class="input" placeholder="12345">
                <x-input-error :messages="$errors->get('postal_code')" class="mt-2" />
            </div>

            <div class="flex items-end">
                <label class="flex cursor-pointer items-center gap-2.5 pb-3 text-sm text-ink-600">
                    <input type="checkbox" name="is_default" value="1" class="rounded border-paper-400 text-amber-500 focus:ring-amber-400" @checked(old('is_default', $address->is_default))>
                    Jadikan alamat utama
                </label>
            </div>
        </div>

        <div class="mt-6 flex flex-col gap-2 border-t border-paper-200 pt-5 sm:flex-row">
            <button type="submit" class="btn-primary sm:flex-1">
                <x-icon name="check" class="h-4 w-4" /> {{ $address->exists ? 'Simpan perubahan' : 'Simpan alamat' }}
            </button>
            <a href="{{ route('customer.addresses.index') }}" class="btn-outline justify-center">Batal</a>
        </div>
    </form>
</x-account-shell>
