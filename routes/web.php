<?php

use App\Enums\Panel;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Auth\PanelAuthController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/

Route::get('media/{path}', [MediaController::class, 'show'])->where('path', '.*')->name('media.show');

Route::get('/', fn () => Inertia::render('welcome/Portal', [
    'panels' => collect(Panel::cases())->map(fn (Panel $panel) => [
        'id' => $panel->value,
        'label' => $panel->label(),
        'loginUrl' => route("panel.{$panel->value}.login"),
        'icon' => match ($panel) {
            Panel::Admin => 'school',
            Panel::Teacher => 'teachers',
            Panel::Student => 'students',
            Panel::Parent => 'parents',
        },
        'description' => match ($panel) {
            Panel::Admin => 'Manage the entire school',
            Panel::Teacher => 'Classes, attendance & results',
            Panel::Student => 'Results, fees & attendance',
            Panel::Parent => 'Track your child\'s progress',
        },
    ]),
]))->name('home');

/*
|--------------------------------------------------------------------------
| Panel Authentication (session isolated per panel)
|--------------------------------------------------------------------------
*/

foreach (Panel::cases() as $panel) {
    Route::prefix($panel->value)->group(function () use ($panel): void {
        Route::get('login', [PanelAuthController::class, 'showLoginForm'])
            ->middleware('guest')
            ->name("panel.{$panel->value}.login");

        Route::post('login', [PanelAuthController::class, 'login'])
            ->middleware('throttle:8,1')
            ->name("panel.{$panel->value}.login.attempt");

        Route::post('logout', [PanelAuthController::class, 'logout'])
            ->middleware("panel:{$panel->value}")
            ->name("panel.{$panel->value}.logout");
    });
}

/*
|--------------------------------------------------------------------------
| Admin Panel
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->middleware('panel:admin')
    ->group(function (): void {
        require __DIR__.'/admin.php';
    });

/*
|--------------------------------------------------------------------------
| Teacher / Student / Parent Portals
|--------------------------------------------------------------------------
*/

foreach (['teacher', 'student', 'parent'] as $portal) {
    Route::prefix($portal)
        ->middleware("panel:{$portal}")
        ->group(function () use ($portal): void {
            require __DIR__."/portals/{$portal}.php";
        });
}
