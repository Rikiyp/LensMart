<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStoreIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $store = $request->user()?->store;

        if (! $store || ! $store->isVerified()) {
            return redirect()
                ->route('partner.store.edit')
                ->with('warning', 'Toko Anda belum terverifikasi. Lengkapi profil dan dokumen terlebih dahulu.');
        }

        return $next($request);
    }
}
