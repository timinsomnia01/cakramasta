<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class EnsureApprovedSeller
{
    /**
     * Pastikan user sudah menjadi penjual yang disetujui.
     * Jika belum, arahkan ke halaman registrasi penjual.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $seller = $request->user()?->seller;

        if (! $seller || ! $seller->isApproved()) {
            Inertia::flash('toast', [
                'type' => 'warning',
                'message' => __('Akun penjual Anda belum disetujui.'),
            ]);

            return to_route('account.seller.show');
        }

        return $next($request);
    }
}
