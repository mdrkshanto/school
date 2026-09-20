<?php

namespace App\Http\Controllers\Portal\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentAttendance;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $student = auth()->user()->student;

        if ($student === null) {
            abort(403, 'No student profile linked to this account.');
        }

        $month = $request->month ?? now()->format('Y-m');

        $records = StudentAttendance::query()
            ->where('student_id', $student->id)
            ->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$month])
            ->orderByDesc('date')
            ->get(['id', 'date', 'status', 'remark']);

        $present = $records->where('status', 'present')->count();
        $absent = $records->where('status', 'absent')->count();

        return Inertia::render('portal/student/Attendance', [
            'records' => $records,
            'month' => $month,
            'summary' => [
                'total' => $records->count(),
                'present' => $present,
                'absent' => $absent,
                'rate' => $records->count() > 0 ? round(($present / $records->count()) * 100, 1) : null,
            ],
        ]);
    }
}
