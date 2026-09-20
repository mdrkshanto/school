<?php

use App\Http\Controllers\Admin\AcademicSessionController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\BookController;
use App\Http\Controllers\Admin\BookIssueController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\ExamScheduleController;
use App\Http\Controllers\Admin\FeeStructureController;
use App\Http\Controllers\Admin\GradeRuleController;
use App\Http\Controllers\Admin\GuardianController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\NoticeController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ResultController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\TransportRouteController;
use App\Http\Controllers\Admin\TransportStopController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VehicleController;
use Illuminate\Support\Facades\Route;

Route::get('dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

// ---- Students -------------------------------------------------------------

Route::get('students', [StudentController::class, 'index'])->middleware('permission:students.view')->name('admin.students.index');
Route::post('students', [StudentController::class, 'store'])->middleware('permission:students.create')->name('admin.students.store');
Route::put('students/{student}', [StudentController::class, 'update'])->middleware('permission:students.update')->name('admin.students.update');
Route::delete('students/{student}', [StudentController::class, 'destroy'])->middleware('permission:students.delete')->name('admin.students.destroy');

// ---- Teachers -------------------------------------------------------------

Route::get('teachers', [TeacherController::class, 'index'])->middleware('permission:teachers.view')->name('admin.teachers.index');
Route::post('teachers', [TeacherController::class, 'store'])->middleware('permission:teachers.create')->name('admin.teachers.store');
Route::put('teachers/{teacher}', [TeacherController::class, 'update'])->middleware('permission:teachers.update')->name('admin.teachers.update');
Route::delete('teachers/{teacher}', [TeacherController::class, 'destroy'])->middleware('permission:teachers.delete')->name('admin.teachers.destroy');

// ---- Guardians ------------------------------------------------------------

Route::get('guardians', [GuardianController::class, 'index'])->middleware('permission:parents.view')->name('admin.guardians.index');
Route::post('guardians', [GuardianController::class, 'store'])->middleware('permission:parents.create')->name('admin.guardians.store');
Route::put('guardians/{guardian}', [GuardianController::class, 'update'])->middleware('permission:parents.update')->name('admin.guardians.update');
Route::delete('guardians/{guardian}', [GuardianController::class, 'destroy'])->middleware('permission:parents.delete')->name('admin.guardians.destroy');

// ---- Classes & Sections ---------------------------------------------------

Route::get('classes', [ClassController::class, 'index'])->middleware('permission:classes.view')->name('admin.classes.index');
Route::post('classes', [ClassController::class, 'store'])->middleware('permission:classes.create')->name('admin.classes.store');
Route::put('classes/{class}', [ClassController::class, 'update'])->middleware('permission:classes.update')->name('admin.classes.update');
Route::delete('classes/{class}', [ClassController::class, 'destroy'])->middleware('permission:classes.delete')->name('admin.classes.destroy');

Route::post('classes/{class}/sections', [SectionController::class, 'store'])->middleware('permission:classes.create')->name('admin.sections.store');
Route::put('sections/{section}', [SectionController::class, 'update'])->middleware('permission:classes.update')->name('admin.sections.update');
Route::delete('sections/{section}', [SectionController::class, 'destroy'])->middleware('permission:classes.delete')->name('admin.sections.destroy');

// ---- Subjects -------------------------------------------------------------

Route::get('subjects', [SubjectController::class, 'index'])->middleware('permission:subjects.view')->name('admin.subjects.index');
Route::post('subjects', [SubjectController::class, 'store'])->middleware('permission:subjects.create')->name('admin.subjects.store');
Route::put('subjects/{subject}', [SubjectController::class, 'update'])->middleware('permission:subjects.update')->name('admin.subjects.update');
Route::delete('subjects/{subject}', [SubjectController::class, 'destroy'])->middleware('permission:subjects.delete')->name('admin.subjects.destroy');

// ---- Academic Sessions ----------------------------------------------------

Route::get('sessions', [AcademicSessionController::class, 'index'])->middleware('permission:sessions.view')->name('admin.sessions.index');
Route::post('sessions', [AcademicSessionController::class, 'store'])->middleware('permission:sessions.create')->name('admin.sessions.store');
Route::put('sessions/{session}', [AcademicSessionController::class, 'update'])->middleware('permission:sessions.update')->name('admin.sessions.update');
Route::delete('sessions/{session}', [AcademicSessionController::class, 'destroy'])->middleware('permission:sessions.delete')->name('admin.sessions.destroy');

// ---- Attendance -----------------------------------------------------------

Route::get('attendance', [AttendanceController::class, 'index'])->middleware('permission:attendance.view')->name('admin.attendance.index');
Route::post('attendance/mark', [AttendanceController::class, 'mark'])->middleware('permission:attendance.manage')->name('admin.attendance.mark');
Route::get('attendance/records', [AttendanceController::class, 'records'])->middleware('permission:attendance.view')->name('admin.attendance.records');
Route::get('attendance/teacher', [AttendanceController::class, 'teacherIndex'])->middleware('permission:attendance.view')->name('admin.attendance.teacher');
Route::post('attendance/teacher/mark', [AttendanceController::class, 'teacherMark'])->middleware('permission:attendance.manage')->name('admin.attendance.teacherMark');

// ---- Exams & Schedules ----------------------------------------------------

Route::get('exams', [ExamController::class, 'index'])->middleware('permission:exams.view')->name('admin.exams.index');
Route::post('exams', [ExamController::class, 'store'])->middleware('permission:exams.create')->name('admin.exams.store');
Route::put('exams/{exam}', [ExamController::class, 'update'])->middleware('permission:exams.update')->name('admin.exams.update');
Route::delete('exams/{exam}', [ExamController::class, 'destroy'])->middleware('permission:exams.delete')->name('admin.exams.destroy');

Route::post('exams/{exam}/schedules', [ExamScheduleController::class, 'store'])->middleware('permission:exams.create')->name('admin.exam-schedules.store');
Route::put('exam-schedules/{schedule}', [ExamScheduleController::class, 'update'])->middleware('permission:exams.update')->name('admin.exam-schedules.update');
Route::delete('exam-schedules/{schedule}', [ExamScheduleController::class, 'destroy'])->middleware('permission:exams.delete')->name('admin.exam-schedules.destroy');

// ---- Grades ---------------------------------------------------------------

Route::get('grade-rules', [GradeRuleController::class, 'index'])->middleware('permission:grades.view')->name('admin.grades.index');
Route::post('grade-rules', [GradeRuleController::class, 'store'])->middleware('permission:grades.create')->name('admin.grades.store');
Route::put('grade-rules/{gradeRule}', [GradeRuleController::class, 'update'])->middleware('permission:grades.update')->name('admin.grades.update');
Route::delete('grade-rules/{gradeRule}', [GradeRuleController::class, 'destroy'])->middleware('permission:grades.delete')->name('admin.grades.destroy');

// ---- Results --------------------------------------------------------------

Route::get('results', [ResultController::class, 'index'])->middleware('permission:results.view')->name('admin.results.index');
Route::get('results/entry', [ResultController::class, 'entry'])->middleware('permission:results.view')->name('admin.results.entry');
Route::post('results/entry', [ResultController::class, 'saveEntry'])->middleware('permission:results.create|results.update')->name('admin.results.save');
Route::post('results/generate', [ResultController::class, 'generate'])->middleware('permission:results.create')->name('admin.results.generate');
Route::get('results/marksheet/{student}/{exam}', [ResultController::class, 'marksheet'])->middleware('permission:results.view')->name('admin.results.marksheet');
Route::post('results/marksheet/{student}/{exam}/pdf', [ResultController::class, 'marksheetPdf'])->middleware('permission:results.view')->name('admin.results.marksheetPdf');

// ---- Fees -----------------------------------------------------------------

Route::get('fee-structures', [FeeStructureController::class, 'index'])->middleware('permission:fees.view')->name('admin.fees.index');
Route::post('fee-structures', [FeeStructureController::class, 'store'])->middleware('permission:fees.create')->name('admin.fees.store');
Route::put('fee-structures/{feeStructure}', [FeeStructureController::class, 'update'])->middleware('permission:fees.update')->name('admin.fees.update');
Route::delete('fee-structures/{feeStructure}', [FeeStructureController::class, 'destroy'])->middleware('permission:fees.delete')->name('admin.fees.destroy');

// ---- Invoices & Payments --------------------------------------------------

Route::get('invoices', [InvoiceController::class, 'index'])->middleware('permission:invoices.view')->name('admin.invoices.index');
Route::post('invoices/generate', [InvoiceController::class, 'generate'])->middleware('permission:invoices.create')->name('admin.invoices.generate');
Route::post('invoices/{invoice}/pay', [InvoiceController::class, 'pay'])->middleware('permission:payments.create')->name('admin.invoices.pay');
Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])->middleware('permission:invoices.delete')->name('admin.invoices.destroy');

// ---- Notices --------------------------------------------------------------

Route::get('notices', [NoticeController::class, 'index'])->middleware('permission:notices.view')->name('admin.notices.index');
Route::post('notices', [NoticeController::class, 'store'])->middleware('permission:notices.create')->name('admin.notices.store');
Route::put('notices/{notice}', [NoticeController::class, 'update'])->middleware('permission:notices.update')->name('admin.notices.update');
Route::delete('notices/{notice}', [NoticeController::class, 'destroy'])->middleware('permission:notices.delete')->name('admin.notices.destroy');

// ---- Events ---------------------------------------------------------------

Route::get('events', [EventController::class, 'index'])->middleware('permission:events.view')->name('admin.events.index');
Route::post('events', [EventController::class, 'store'])->middleware('permission:events.create')->name('admin.events.store');
Route::put('events/{event}', [EventController::class, 'update'])->middleware('permission:events.update')->name('admin.events.update');
Route::delete('events/{event}', [EventController::class, 'destroy'])->middleware('permission:events.delete')->name('admin.events.destroy');

// ---- Library --------------------------------------------------------------

Route::get('books', [BookController::class, 'index'])->middleware('permission:library.view')->name('admin.books.index');
Route::post('books', [BookController::class, 'store'])->middleware('permission:library.create')->name('admin.books.store');
Route::put('books/{book}', [BookController::class, 'update'])->middleware('permission:library.update')->name('admin.books.update');
Route::delete('books/{book}', [BookController::class, 'destroy'])->middleware('permission:library.delete')->name('admin.books.destroy');

Route::get('book-issues', [BookIssueController::class, 'index'])->middleware('permission:library.view')->name('admin.book-issues.index');
Route::post('book-issues', [BookIssueController::class, 'store'])->middleware('permission:library.create')->name('admin.book-issues.store');
Route::post('book-issues/{issue}/return', [BookIssueController::class, 'returnBook'])->middleware('permission:library.update')->name('admin.book-issues.return');

// ---- Transport ------------------------------------------------------------

Route::get('routes', [TransportRouteController::class, 'index'])->middleware('permission:transport.view')->name('admin.routes.index');
Route::post('routes', [TransportRouteController::class, 'store'])->middleware('permission:transport.create')->name('admin.routes.store');
Route::put('routes/{route}', [TransportRouteController::class, 'update'])->middleware('permission:transport.update')->name('admin.routes.update');
Route::delete('routes/{route}', [TransportRouteController::class, 'destroy'])->middleware('permission:transport.delete')->name('admin.routes.destroy');

Route::post('routes/{route}/stops', [TransportStopController::class, 'store'])->middleware('permission:transport.create')->name('admin.stops.store');
Route::put('stops/{stop}', [TransportStopController::class, 'update'])->middleware('permission:transport.update')->name('admin.stops.update');
Route::delete('stops/{stop}', [TransportStopController::class, 'destroy'])->middleware('permission:transport.delete')->name('admin.stops.destroy');

Route::get('vehicles', [VehicleController::class, 'index'])->middleware('permission:transport.view')->name('admin.vehicles.index');
Route::post('vehicles', [VehicleController::class, 'store'])->middleware('permission:transport.create')->name('admin.vehicles.store');
Route::put('vehicles/{vehicle}', [VehicleController::class, 'update'])->middleware('permission:transport.update')->name('admin.vehicles.update');
Route::delete('vehicles/{vehicle}', [VehicleController::class, 'destroy'])->middleware('permission:transport.delete')->name('admin.vehicles.destroy');

// ---- Users & Roles --------------------------------------------------------

Route::get('users', [UserController::class, 'index'])->middleware('permission:users.manage')->name('admin.users.index');
Route::post('users', [UserController::class, 'store'])->middleware('permission:users.manage')->name('admin.users.store');
Route::put('users/{user}', [UserController::class, 'update'])->middleware('permission:users.manage')->name('admin.users.update');
Route::delete('users/{user}', [UserController::class, 'destroy'])->middleware('permission:users.manage')->name('admin.users.destroy');

Route::get('roles', [RoleController::class, 'index'])->middleware('permission:roles.manage')->name('admin.roles.index');
Route::put('roles/{role}/permissions', [RoleController::class, 'updatePermissions'])->middleware('permission:roles.manage')->name('admin.roles.permissions');

// ---- Settings & Reports ---------------------------------------------------

Route::get('settings', [SettingController::class, 'index'])->middleware('permission:settings.manage')->name('admin.settings.index');
Route::put('settings', [SettingController::class, 'update'])->middleware('permission:settings.manage')->name('admin.settings.update');

Route::get('reports', [ReportController::class, 'index'])->middleware('permission:reports.view')->name('admin.reports.index');
Route::get('reports/students', [ReportController::class, 'students'])->middleware('permission:reports.view')->name('admin.reports.students');
Route::get('reports/fees', [ReportController::class, 'fees'])->middleware('permission:reports.view')->name('admin.reports.fees');
Route::get('reports/attendance', [ReportController::class, 'attendance'])->middleware('permission:reports.view')->name('admin.reports.attendance');
Route::get('reports/results', [ReportController::class, 'results'])->middleware('permission:reports.view')->name('admin.reports.results');
