<?php

use App\Http\Controllers\Portal\ProfileController;
use App\Http\Controllers\Portal\Teacher\AttendanceController;
use App\Http\Controllers\Portal\Teacher\DashboardController;
use App\Http\Controllers\Portal\Teacher\NoticeController;
use App\Http\Controllers\Portal\Teacher\ResultController;
use Illuminate\Support\Facades\Route;

Route::get('dashboard', [DashboardController::class, 'index'])->name('teacher.dashboard');

Route::get('attendance', [AttendanceController::class, 'index'])->name('teacher.attendance.index');
Route::post('attendance/mark', [AttendanceController::class, 'mark'])->name('teacher.attendance.mark');

Route::get('results', [ResultController::class, 'index'])->name('teacher.results.index');
Route::post('results/save', [ResultController::class, 'save'])->name('teacher.results.save');
Route::get('results/marksheet/{student}/{exam}', [ResultController::class, 'marksheet'])->name('teacher.results.marksheet');

Route::get('notices', [NoticeController::class, 'index'])->name('teacher.notices.index');

Route::get('profile', [ProfileController::class, 'show'])->name('teacher.profile');
Route::put('profile', [ProfileController::class, 'update'])->name('teacher.profile.update');
