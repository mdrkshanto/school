<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\FeeInvoice;
use App\Models\Result;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Services\DashboardService;
use App\Services\ResultService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ParentController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboard,
        private readonly ResultService $results,
    ) {}

    public function index()
    {
        return Inertia::render('portal/parent/Dashboard', [
            'data' => $this->dashboard->parent(auth()->user()),
        ]);
    }

    public function childDetails(Request $request, Student $student)
    {
        $guardian = auth()->user()->guardian;

        abort_if($guardian === null || ! $guardian->students()->where('students.id', $student->id)->exists(), 403);

        $curriculum = auth()->user()->student;

        $student->load(['class:id,name', 'section:id,name', 'session:id,name']);

        $attendance = StudentAttendance::query()
            ->where('student_id', $student->id)
            ->orderByDesc('date')
            ->limit(30)
            ->get(['id', 'date', 'status', 'remark']);

        $invoices = FeeInvoice::query()
            ->where('student_id', $student->id)
            ->with('feeStructure:id,name')
            ->orderByDesc('id')
            ->get();

        $exams = Exam::query()
            ->where('status', 'published')
            ->whereHas('schedules', fn ($q) => $q->where('class_id', $student->class_id))
            ->get()
            ->map(function (Exam $exam) use ($student) {
                $exists = Result::query()->where('exam_id', $exam->id)->where('student_id', $student->id)->exists();

                return [
                    'id' => $exam->id,
                    'name' => $exam->name,
                    'available' => $exists,
                    'marksheet' => $exists ? $this->results->marksheet($student, $exam) : null,
                ];
            });

        $route = $student->transport()->with(['route:id,name', 'stop:id,name'])->where('is_active', true)->first();

        return Inertia::render('portal/parent/ChildDetails', [
            'student' => $student,
            'attendance' => $attendance,
            'attendance_rate' => $attendance->count() > 0
                ? round(($attendance->where('status', 'present')->count() / $attendance->count()) * 100, 1)
                : null,
            'invoices' => $invoices->map(fn (FeeInvoice $invoice) => [
                'id' => $invoice->id,
                'invoice_no' => $invoice->invoice_no,
                'structure' => $invoice->feeStructure?->name,
                'amount' => (float) $invoice->amount,
                'paid_amount' => (float) $invoice->paid_amount,
                'balance' => $invoice->balance,
                'status' => $invoice->status,
            ]),
            'fee_balance' => (float) $invoices->sum(fn (FeeInvoice $invoice) => $invoice->balance),
            'exams' => $exams,
            'transport' => $route ? ['route' => $route->route?->name, 'stop' => $route->stop?->name] : null,
        ]);
    }
}
