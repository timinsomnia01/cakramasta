<?php

namespace App\Http\Middleware;

use App\Models\Product;
use App\Models\Visit;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordVisit
{
    /**
     * Catat kunjungan halaman publik untuk statistik dashboard admin.
     * Hanya request GET yang berhasil dan bukan prefetch yang dihitung.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (
            $request->isMethod('GET')
            && $response->isSuccessful()
            && $request->header('Purpose') !== 'prefetch'
            && ! $request->header('X-Inertia-Partial-Component')
        ) {
            $product = $request->route('product');

            Visit::record($request, $product instanceof Product ? $product : null);
        }

        return $response;
    }
}
