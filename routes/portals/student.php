<?php

use App\Http\Controllers\Portal\ProfileController;
use App\Http\Controllers\Portal\Student\AttendanceController;
use App\Http\Controllers\Portal\Student\DashboardController;
use App\Http\Controllers\Portal\Student\FeeController;
use App\Http\Controllers\Portal\Student\NoticeController;
use App\Http\Controllers\Portal\Student\ResultController;
use Illuminate\Support\Facades\Route;

Route::get('dashboard', [DashboardController::class, 'index'])->name('student.dashboard');

Route::get('results', [ResultController::class, 'index'])->name('student.results.index');
Route::get('results/{exam}', [ResultController::class, 'show'])->name('student.results.show');

Route::get('fees', [FeeController::class, 'index'])->name('student.fees.index');

Route::get('attendance', [AttendanceController::class, 'index'])->name('student.attendance.index');

Route::get('notices', [NoticeController::class, 'index'])->name('student.notices.index');

Route::get('profile', [ProfileController::class, 'show'])->name('student.profile');
Route::put('profile', [ProfileController::class, 'update'])->name('student.profile.update');
