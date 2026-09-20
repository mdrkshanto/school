<?php

namespace App\Http\Middleware;

use App\Enums\Panel;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePanelAuthentication
{
    public function handle(Request $request, Closure $next, string $panelName): Response
    {
        $panel = Panel::tryFrom($panelName);

        if ($panel === null) {
            abort(404);
        }

        $user = Auth::guard($panel->value)->user();

        if ($user === null || ! $user->is_active || ! $user->hasRole($panel->role())) {
            return redirect()->guest(route(Panel::loginRoute($panel)));
        }

        Auth::shouldUse($panel->value);

        return $next($request);
    }
}