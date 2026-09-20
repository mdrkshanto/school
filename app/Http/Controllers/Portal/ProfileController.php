<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\ProfileRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user()->only(['id', 'name', 'email', 'phone', 'avatar', 'created_at']);

        return Inertia::render('portal/Profile', [
            'user' => $user,
        ]);
    }

    public function update(ProfileRequest $request)
    {
        $user = $request->user();

        $user->update($request->safe()->except(['password']));

        if ($request->filled('password')) {
            $user->update(['password' => $request->password]);
        }

        $this->successFlash('Profile updated.');

        return back();
    }
}
