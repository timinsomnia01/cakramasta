<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Pastikan user aktif dan memiliki salah satu role yang diizinkan.
     *
     * Contoh pemakaian pada route: ->middleware('role:admin,verifikator')
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        abort_if(! $user, 401);
        abort_if(! $user->is_active, 403, 'Akun Anda dinonaktifkan.');

        $allowed = array_filter(array_map(fn (string $role) => UserRole::tryFrom($role), $roles));

        abort_unless($user->hasRole(...$allowed), 403);

        return $next($request);
    }
}
