<?php

namespace App\Http\Middleware;

use App\Enums\Panel;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Runs before the session starts (prepended to the web group) and isolates
 * sessions per panel by switching the session cookie name and the default
 * auth guard based on the request's path prefix.
 */
class SetPanelContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $prefix = str($request->path())->before('/')->toString();

        $panel = Panel::tryFrom($prefix);

        if ($panel === null) {
            config(['auth.defaults.guard' => 'web']);
            config(['auth.defaults.passwords' => 'users']);
            config(['session.cookie' => 'laravel_session']);

            return $next($request);
        }

        config(['auth.defaults.guard' => $panel->value]);
        config(['auth.defaults.passwords' => 'users']);
        config(['session.cookie' => $panel->value.'_session']);

        Auth::shouldUse($panel->value);

        return $next($request);
    }
}