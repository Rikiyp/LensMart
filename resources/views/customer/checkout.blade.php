<x-account-shell title="Checkout" subtitle="Pilih alamat dan metode pengiriman, lalu selesaikan pembayaran.">
    @if ($addresses->isEmpty())
        <x-empty-state icon="map-pin" title="Belum ada alamat pengiriman" description="Tambahkan alamat terlebih dahulu sebelum melanjutkan checkout.">
            <a href="{{ route('customer.addresses.create') }}" class="btn-primary btn-sm">Tambah alamat</a>
        </x-empty-state>
    @else
        <form method="POST" action="{{ route('customer.checkout.store') }}"
              x-data="{
                  address: '{{ $defaultAddress?->id }}',
                  shipping: '{{ array_key_first($shippingMethods) }}',
                  costs: @js(collect($shippingMethods)->map(fn ($method) => $method['cost'])),
                  storeCount: {{ $groups->count() }},
                  subtotal: {{ $subtotal }},
                  rupiah(value) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(value)); },
                  get shippingTotal() { return (this.costs[this.shipping] ?? 0) * this.storeCount; },
                  get grandTotal() { return this.subtotal + this.shippingTotal; }
              }">
            @csrf

            <div class="grid gap-6 lg:grid-cols-[1fr_340px]">
                <div class="space-y-5">
                    {{-- Address --}}
                    <section class="card p-5">
                        <div class="flex items-center justify-between">
                            <h2 class="flex items-center gap-2 text-sm font-extrabold text-ink-950">
                                <x-icon name="map-pin" class="h-4 w-4 text-amber-500" /> Alamat pengiriman
                            </h2>
                            <a href="{{ route('customer.addresses.create') }}" class="text-xs font-bold text-amber-600">+ Alamat baru</a>
                        </div>
                        <x-input-error :messages="$errors->get('address_id')" class="mt-2" />

                        <div class="mt-4 space-y-2">
                            @foreach ($addresses as $address)
                                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-paper-300 p-4 transition hover:border-amber-300 has-[:checked]:border-amber-400 has-[:checked]:bg-amber-50/40">
                                    <input type="radio" name="address_id" value="{{ $address->id }}" x-model="address" class="mt-0.5 border-paper-400 text-amber-500 focus:ring-amber-400">
                                    <span class="min-w-0 flex-1">
                                        <span class="flex flex-wrap items-center gap-2">
                                            <span class="text-sm font-bold text-ink-950">{{ $address->label }}</span>
                                            @if ($address->is_default)
                                                <span class="badge bg-amber-100 text-amber-800 ring-amber-600/20">Utama</span>
                                            @endif
                                        </span>
                                        <span class="mt-1 block text-xs font-semibold text-ink-700">{{ $address->recipient_name }} · {{ $address->phone }}</span>
                                        <span class="mt-0.5 block text-xs leading-relaxed text-ink-500">{{ $address->full_address }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </section>

                    {{-- Shipping --}}
                    <section class="card p-5">
                        <h2 class="flex items-center gap-2 text-sm font-extrabold text-ink-950">
                            <x-icon name="truck" class="h-4 w-4 text-amber-500" /> Metode pengiriman
                        </h2>
                        <p class="mt-1 text-[11px] text-ink-400">Ongkir dikenakan per toko. Pesanan dari toko berbeda dikirim terpisah.</p>
                        <x-input-error :messages="$errors->get('shipping_method')" class="mt-2" />

                        <div class="mt-4 grid gap-2 sm:grid-cols-2">
                            @foreach ($shippingMethods as $key => $method)
                                <label class="flex cursor-pointer items-center justify-between gap-3 rounded-xl border border-paper-300 p-4 transition hover:border-amber-300 has-[:checked]:border-amber-400 has-[:checked]:bg-amber-50/40">
                                    <span class="flex items-center gap-3">
                                        <input type="radio" name="shipping_method" value="{{ $key }}" x-model="shipping" class="border-paper-400 text-amber-500 focus:ring-amber-400">
                                        <span>
                                            <span class="block text-xs font-bold text-ink-950">{{ $method['label'] }}</span>
                                            <span class="block text-[11px] text-ink-400">{{ $method['eta'] }}</span>
                                        </span>
                                    </span>
                                    <span class="text-xs font-bold text-ink-950">{{ format_rupiah($method['cost']) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </section>

                    {{-- Items --}}
                    <section class="card overflow-hidden">
                        <div class="flex items-center justify-between gap-3 border-b border-paper-200 px-5 py-4">
                            <div>
                                <h2 class="text-sm font-extrabold text-ink-950">Ringkasan per toko</h2>
                                <p class="mt-1 text-[11px] text-ink-400">{{ $itemCount }} item dipilih · dipecah per toko</p>
                            </div>
                            <a href="{{ route('customer.cart.index') }}" class="shrink-0 text-[10px] font-semibold uppercase tracking-[0.12em] text-ink-500 transition hover:text-ink-950">Ubah pilihan</a>
                        </div>
                        <div class="divide-y divide-paper-200">
                            @foreach ($groups as $group)
                                <div class="p-5">
                                    <div class="flex items-center gap-2">
                                        <img src="{{ $group['store']->logo_url }}" alt="" class="h-7 w-7 rounded-lg object-cover">
                                        <p class="text-xs font-bold text-ink-950">{{ $group['store']->name }}</p>
                                        <span class="ml-auto text-xs font-semibold text-ink-600">{{ format_rupiah($group['subtotal']) }}</span>
                                    </div>
                                    <div class="mt-3 space-y-2">
                                        @foreach ($group['items'] as $item)
                                            <div class="flex items-center gap-3 text-xs">
                                                <img src="{{ $item->product->image_url }}" alt="" class="h-10 w-10 rounded-lg object-cover">
                                                <div class="min-w-0 flex-1">
                                                    <p class="truncate font-semibold text-ink-700">{{ $item->product->name }}</p>
                                                    <p class="text-[11px] text-ink-400">{{ $item->quantity }} × {{ format_rupiah($item->product->price) }}</p>
                                                </div>
                                                <span class="font-semibold text-ink-700">{{ format_rupiah($item->product->price * $item->quantity) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="card p-5">
                        <label for="note" class="label">Catatan untuk toko <span class="font-normal text-ink-400">(opsional)</span></label>
                        <textarea id="note" name="note" rows="2" class="textarea" maxlength="500" placeholder="Contoh: tolong packing bubble wrap tambahan">{{ old('note') }}</textarea>
                        <x-input-error :messages="$errors->get('note')" class="mt-2" />
                    </section>
                </div>

                <aside class="h-fit lg:sticky lg:top-24">
                    <div class="card p-5">
                        <h2 class="text-sm font-extrabold text-ink-950">Total pembayaran</h2>
                        <dl class="mt-4 space-y-2.5 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-ink-500">Subtotal produk</dt>
                                <dd class="font-semibold text-ink-950">{{ format_rupiah($subtotal) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-ink-500">Ongkir (<span x-text="storeCount"></span> toko)</dt>
                                <dd class="font-semibold text-ink-950" x-text="rupiah(shippingTotal)"></dd>
                            </div>
                            <div class="flex justify-between text-xs text-ink-400">
                                <dt>Komisi platform (5%)</dt>
                                <dd>Ditanggung penjual</dd>
                            </div>
                        </dl>
                        <div class="mt-4 flex items-end justify-between border-t border-paper-200 pt-4">
                            <span class="text-xs font-bold text-ink-500">Total</span>
                            <span class="text-2xl font-extrabold tracking-tight text-ink-950" x-text="rupiah(grandTotal)"></span>
                        </div>
                        <button type="submit" class="btn-primary mt-5 w-full py-3">
                            Buat pesanan <x-icon name="arrow-right" class="h-4 w-4" />
                        </button>
                        <p class="mt-3 flex items-start gap-1.5 text-[11px] leading-relaxed text-ink-400">
                            <x-icon name="lock-closed" class="mt-0.5 h-3.5 w-3.5 shrink-0" />
                            Anda akan diarahkan ke halaman pembayaran simulasi. Selesaikan dalam 24 jam.
                        </p>
                    </div>
                </aside>
            </div>
        </form>
    @endif
</x-account-shell>
