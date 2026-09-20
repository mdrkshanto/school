<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PermissionFamily;
use App\Http\Controllers\Controller;
use App\Models\BookIssue;
use App\Models\Exam;
use App\Models\FeeInvoice;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/Reports/Index', [
            'stats' => [
                'students' => Student::count(),
                'teachers' => Teacher::count(),
                'defaulters' => FeeInvoice::query()->whereIn('status', ['pending', 'partial'])->count(),
                'overdue_books' => BookIssue::query()->where('status', 'issued')->where('due_date', '<', now())->count(),
            ],
            'permissionGroups' => collect(PermissionFamily::cases())->map(fn (PermissionFamily $f) => $f->value)->values(),
        ]);
    }

    public function students(Request $request)
    {
        $query = Student::query()
            ->with(['class:id,name', 'section:id,name', 'session:id,name'])
            ->when($request->class_id, fn ($q, $v) => $q->where('class_id', $v))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->orderByDesc('id');

        return Inertia::render('admin/Reports/Students', [
            'students' => $this->paginated($query->paginate(15)->withQueryString()),
            'classes' => SchoolClass::select('id', 'name')->orderBy('name')->get(),
            'filters' => $request->only(['class_id', 'status']),
        ]);
    }

    public function fees(Request $request)
    {
        $month = $request->month ?? now()->format('Y-m');

        $collected = FeeInvoice::query()
            ->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$month])
            ->sum('paid_amount');

        $pending = FeeInvoice::query()->whereIn('status', ['pending', 'partial'])->sum(DB::raw('amount - paid_amount'));

        $byStatus = DB::table('fee_invoices')
            ->select('status', DB::raw('COUNT(*) as total'), DB::raw('SUM(amount) as amount'), DB::raw('SUM(paid_amount) as paid'))
            ->groupBy('status')
            ->get();

        $recent = FeeInvoice::query()
            ->with(['student:id,name,admission_no', 'feeStructure:id,name'])
            ->orderByDesc('id')
            ->limit(15)
            ->get();

        return Inertia::render('admin/Reports/Fees', [
            'summary' => [
                'collected' => round($collected, 2),
                'pending' => round($pending, 2),
            ],
            'byStatus' => $byStatus,
            'recent' => $recent,
            'month' => $month,
        ]);
    }

    public function attendance(Request $request)
    {
        $month = $request->month ?? now()->format('Y-m');

        $dailyTotals = StudentAttendance::query()
            ->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$month])
            ->select('date', DB::raw('COUNT(*) as total'), DB::raw('SUM(status = "present") as present'), DB::raw('SUM(status = "absent") as absent'), DB::raw('SUM(status IN ("late", "leave")) as late'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date' => $row->date,
                'total' => (int) $row->total,
                'present' => (int) $row->present,
                'absent' => (int) $row->absent,
                'late' => (int) $row->late,
            ]);

        return Inertia::render('admin/Reports/Attendance', [
            'dailyTotals' => $dailyTotals,
            'month' => $month,
            'summary' => [
                'present' => (int) config('app.present'),
                'absent' => StudentAttendance::query()->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$month])->where('status', 'absent')->count(),
            ],
        ]);
    }

    public function results(Request $request)
    {
        $examId = $request->integer('exam_id', 0);

        $query = Result::query()
            ->with('student:id,name,admission_no', 'exam:id,name', 'subject:id,name')
            ->when($examId, fn ($q, $v) => $q->where('exam_id', $v))
            ->whereNotNull('obtained_marks')
            ->orderByDesc('obtained_marks');

        return Inertia::render('admin/Reports/Results', [
            'results' => $this->paginated($query->paginate(15)->withQueryString()),
            'exams' => Exam::select('id', 'name')->get(),
            'filters' => $request->only(['exam_id']),
        ]);
    }
}
