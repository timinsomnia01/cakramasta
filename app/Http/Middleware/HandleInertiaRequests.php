<?php

namespace App\Http\Middleware;

use App\Models\CartItem;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'currentTeam' => fn () => $user?->currentTeam ? $user->toUserTeam($user->currentTeam) : null,
            'teams' => fn () => $user?->toUserTeams(includeCurrent: true) ?? [],
            'marketplace' => fn () => [
                'role' => $user?->role?->value,
                'isSeller' => (bool) $user?->isSeller(),
                'sellerStatus' => $user?->seller?->status->value,
                'cartCount' => $user ? (int) CartItem::query()->where('user_id', $user->id)->sum('quantity') : 0,
                'footerPages' => Cache::remember('footer-pages', 600, fn () => Page::query()
                    ->published()
                    ->orderBy('sort_order')
                    ->get(['title', 'slug'])
                    ->map(fn (Page $page) => [
                        'title' => $page->title,
                        'slug' => $page->slug,
                        'href' => route('pages.show', $page, absolute: false),
                    ])
                    ->all()),
            ],
        ];
    }
}
