<x-layouts.public title="Profil Akun">
    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="section-eyebrow">Pengaturan akun</p>
                <h1 class="mt-2 font-display text-4xl font-semibold tracking-tight text-ink-950">Profil akun</h1>
                <p class="mt-2 text-sm text-ink-500">Perbarui data diri, foto profil, dan keamanan akun Anda.</p>
            </div>
            <a href="{{ route($user->role->dashboardRoute()) }}" class="btn-outline btn-sm">
                <x-icon name="squares" class="h-3.5 w-3.5" /> Ke dashboard
            </a>
        </div>

        <div class="mt-6 grid gap-5 lg:grid-cols-[280px_1fr]">
            <aside class="space-y-4">
                <div class="card p-5 text-center">
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="mx-auto h-24 w-24 rounded-full object-cover ring-4 ring-amber-100">
                    <p class="mt-4 text-base font-extrabold text-ink-950">{{ $user->name }}</p>
                    <p class="text-xs text-ink-500">{{ $user->email }}</p>
                    <span class="badge mt-3 bg-amber-50 text-amber-700 ring-amber-600/20">{{ $user->role->label() }}</span>
                    @if ($user->store)
                        <p class="mt-3 flex items-center justify-center gap-1 text-[11px] font-semibold text-ink-500">
                            <x-icon name="store" class="h-3.5 w-3.5" /> {{ $user->store->name }}
                        </p>
                    @endif
                    <p class="mt-3 border-t border-paper-200 pt-3 text-[10px] text-ink-400">Bergabung {{ format_tanggal($user->created_at) }}</p>
                </div>

                <div class="card p-4">
                    <p class="text-xs font-bold text-ink-950">Status akun</p>
                    <div class="mt-2 flex items-center justify-between text-[11px]">
                        <span class="text-ink-500">Aktif</span>
                        <span class="badge bg-emerald-50 text-emerald-700 ring-emerald-600/20">{{ $user->is_active ? 'Ya' : 'Tidak' }}</span>
                    </div>
                    <div class="mt-1.5 flex items-center justify-between text-[11px]">
                        <span class="text-ink-500">Email terverifikasi</span>
                        <span class="font-semibold text-ink-700">{{ $user->email_verified_at ? 'Ya' : 'Belum' }}</span>
                    </div>
                </div>
            </aside>

            <div class="space-y-5">
                {{-- Profile --}}
                <section class="card p-5 sm:p-6">
                    <div class="border-b border-paper-200 pb-4">
                        <h2 class="text-sm font-extrabold text-ink-950">Informasi profil</h2>
                        <p class="mt-1 text-xs text-ink-500">Data ini digunakan untuk komunikasi pesanan dan identitas akun.</p>
                    </div>

                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                        @csrf
                        @method('PATCH')

                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="h-16 w-16 rounded-full object-cover ring-2 ring-paper-200">
                            <div class="flex-1">
                                <label for="avatar" class="label">Foto profil <span class="font-normal text-ink-400">(JPG/PNG/WebP, maks. 2 MB)</span></label>
                                <input id="avatar" type="file" name="avatar" accept="image/jpeg,image/png,image/webp"
                                       class="block w-full rounded-xl border border-dashed border-paper-400 bg-white px-4 py-2.5 text-xs text-ink-500 file:mr-3 file:rounded-lg file:border-0 file:bg-ink-950 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white">
                                <x-input-error :messages="$errors->get('avatar')" class="mt-2" />
                                @if ($user->avatar)
                                    <label class="mt-2 flex cursor-pointer items-center gap-2 text-[11px] text-ink-500">
                                        <input type="checkbox" name="remove_avatar" value="1" class="rounded border-paper-400 text-rose-500 focus:ring-rose-400">
                                        Hapus foto profil saat ini
                                    </label>
                                @endif
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="name" class="label">Nama lengkap</label>
                                <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name" class="input">
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>
                            <div>
                                <label for="phone" class="label">Nomor telepon</label>
                                <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}" autocomplete="tel" class="input" placeholder="08xxxxxxxxxx">
                                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                            </div>
                            <div class="sm:col-span-2">
                                <label for="email" class="label">Email</label>
                                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="username" class="input">
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                    <p class="mt-2 text-[11px] text-amber-700">Email belum diverifikasi. Perubahan email akan memerlukan verifikasi ulang.</p>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-3 border-t border-paper-200 pt-4">
                            <button type="submit" class="btn-primary">
                                <x-icon name="check" class="h-4 w-4" /> Simpan profil
                            </button>
                            @if (session('status') === 'profile-updated')
                                <span class="text-xs font-semibold text-emerald-600">Tersimpan.</span>
                            @endif
                        </div>
                    </form>
                </section>

                {{-- Password --}}
                <section class="card p-5 sm:p-6">
                    <div class="border-b border-paper-200 pb-4">
                        <h2 class="text-sm font-extrabold text-ink-950">Keamanan akun</h2>
                        <p class="mt-1 text-xs text-ink-500">Gunakan password yang panjang dan unik untuk melindungi akun.</p>
                    </div>

                    <form method="POST" action="{{ route('password.update') }}" class="mt-5 space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="current_password" class="label">Password saat ini</label>
                            <input id="current_password" type="password" name="current_password" autocomplete="current-password" class="input">
                            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="password" class="label">Password baru</label>
                                <input id="password" type="password" name="password" autocomplete="new-password" class="input" placeholder="Minimal 8 karakter">
                                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
                            </div>
                            <div>
                                <label for="password_confirmation" class="label">Konfirmasi password baru</label>
                                <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" class="input">
                                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center gap-3 border-t border-paper-200 pt-4">
                            <button type="submit" class="btn-primary">
                                <x-icon name="lock-closed" class="h-4 w-4" /> Perbarui password
                            </button>
                            @if (session('status') === 'password-updated')
                                <span class="text-xs font-semibold text-emerald-600">Password diperbarui.</span>
                            @endif
                        </div>
                    </form>
                </section>

                {{-- Delete --}}
                @unless ($user->isAdmin())
                    <section class="card border-rose-200 p-5 sm:p-6">
                        <div class="border-b border-rose-100 pb-4">
                            <h2 class="text-sm font-extrabold text-rose-700">Hapus akun</h2>
                            <p class="mt-1 text-xs leading-relaxed text-ink-500">Semua data akun, pesanan, dan ulasan akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.</p>
                        </div>

                        <form method="POST" action="{{ route('profile.destroy') }}" class="mt-5"
                              x-confirm="{ title: 'Hapus akun permanen?', message: 'Seluruh data akun, pesanan, dan ulasan akan dihapus dan tidak dapat dipulihkan.', confirm: 'Ya, hapus akun', tone: 'danger' }">
                            @csrf
                            @method('DELETE')

                            <label for="delete_password" class="label">Konfirmasi dengan password</label>
                            <div class="flex flex-col gap-2 sm:flex-row">
                                <input id="delete_password" type="password" name="password" autocomplete="current-password" class="input sm:max-w-xs" placeholder="Password Anda" required>
                                <button type="submit" class="btn-danger shrink-0">
                                    <x-icon name="trash" class="h-4 w-4" /> Hapus akun
                                </button>
                            </div>
                            <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
                        </form>
                    </section>
                @endunless
            </div>
        </div>
    </div>
</x-layouts.public>
