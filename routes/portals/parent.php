<?php

use App\Http\Controllers\Portal\ParentController;
use App\Http\Controllers\Portal\ProfileController;
use App\Http\Controllers\Portal\Student\NoticeController;
use Illuminate\Support\Facades\Route;

Route::get('dashboard', [ParentController::class, 'index'])->name('parent.dashboard');

Route::get('children/{student}', [ParentController::class, 'childDetails'])->name('parent.children.show');

Route::get('notices', [NoticeController::class, 'index'])->name('parent.notices.index');

Route::get('profile', [ProfileController::class, 'show'])->name('parent.profile');
Route::put('profile', [ProfileController::class, 'update'])->name('parent.profile.update');
