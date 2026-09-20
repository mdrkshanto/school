<?php

namespace App\Http\Middleware;

use App\Enums\Panel;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $panel = Panel::fromRoute(str($request->path())->before('/')->toString());
        $user = Auth::user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'panel' => $panel->value,
            'flash' => [
                'success' => session('flash.success'),
                'error' => session('flash.error'),
                'warning' => session('flash.warning'),
            ],
            'publicSettings' => Setting::query()
                ->where('is_public', true)
                ->pluck('value', 'key'),
            'auth' => [
                'user' => $user ? collect($user->toArray())
                    ->only(['id', 'name', 'email', 'phone', 'avatar'])
                    ->put('roles', $user->getRoleNames())
                    ->all() : null,
                'permissions' => $user ? $user->getAllPermissions()->pluck('name') : [],
            ],
        ];
    }
}