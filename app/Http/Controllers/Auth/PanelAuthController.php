<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Panel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PanelAuthController extends Controller
{
    private function panelFromRequest(Request $request): Panel
    {
        $segments = $request->segments();
        $first = $segments[0] ?? '';
        $panel = Panel::tryFrom($first);

        if ($panel === null) {
            abort(404);
        }

        return $panel;
    }

    public function showLoginForm(Request $request): Response
    {
        $panel = $this->panelFromRequest($request);

        if (Auth::guard($panel->value)->check()) {
            return Inertia::location(route("{$panel->value}.dashboard"));
        }

        return Inertia::render('auth/Login', [
            'panel' => $panel->value,
            'panelLabel' => $panel->label(),
        ]);
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $panel = $this->panelFromRequest($request);

        if (Auth::guard($panel->value)->attempt($request->credentials(), (bool) $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::guard($panel->value)->user();

            if (! $user->is_active || ! $user->hasRole($panel->role())) {
                Auth::guard($panel->value)->logout();
                $request->session()->invalidate();

                return back()->withErrors([
                    'email' => 'Your account is not authorized for this panel.',
                ])->onlyInput('email');
            }

            return redirect()->intended(route("{$panel->value}.dashboard"));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        $panel = $this->panelFromRequest($request);

        Auth::guard($panel->value)->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route(Panel::loginRoute($panel));
    }
}
