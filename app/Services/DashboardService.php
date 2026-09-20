<?php

namespace App\Services;

use App\Models\AcademicSession;
use App\Models\Book;
use App\Models\Event;
use App\Models\Exam;
use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\Guardian;
use App\Models\Notice;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Teacher;
use App\Models\User;

class DashboardService
{
    /**
     * Aggregate data used by the admin dashboard.
     *
     * @return array<string, mixed>
     */
    public function admin(): array
    {
        $sessionId = AcademicSession::query()->active()->value('id');
        $today = now()->toDateString();

        $todayAttendance = StudentAttendance::query()
            ->when($sessionId, fn ($q) => $q->where('session_id', $sessionId))
            ->where('date', $today)
            ->selectRaw('count(*) as total')
            ->selectRaw("sum(case when status = 'present' then 1 else 0 end) as present")
            ->selectRaw("sum(case when status = 'absent' then 1 else 0 end) as absent")
            ->first();

        $studentsPerClass = Student::query()
            ->when($sessionId, fn ($q) => $q->where('session_id', $sessionId))
            ->with('class:id,name')
            ->get()
            ->groupBy(fn (Student $s) => $s->class?->name ?? 'Unassigned')
            ->map(fn ($group) => $group->count());

        $collections = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $collections[] = [
                'month' => $month->format('M y'),
                'amount' => (float) FeePayment::query()
                    ->whereYear('paid_at', $month->year)
                    ->whereMonth('paid_at', $month->month)
                    ->sum('amount'),
            ];
        }

        return [
            'totals' => [
                'students' => Student::query()->where('status', 'active')->count(),
                'teachers' => Teacher::query()->where('status', 'active')->count(),
                'guardians' => Guardian::query()->where('status', 'active')->count(),
                'classes' => SchoolClass::query()->where('status', 'active')->count(),
                'sessions' => AcademicSession::query()->count(),
                'books' => Book::query()->count(),
            ],
            'attendance_today' => $todayAttendance ? [
                'total' => (int) $todayAttendance->total,
                'present' => (int) $todayAttendance->present,
                'absent' => (int) $todayAttendance->absent,
            ] : ['total' => 0, 'present' => 0, 'absent' => 0],
            'students_per_class' => $studentsPerClass,
            'collections' => $collections,
            'fee_summary' => [
                'collected' => (float) FeePayment::query()->sum('amount'),
                'outstanding' => (float) FeeInvoice::query()
                    ->whereIn('status', [FeeInvoice::STATUS_PENDING, FeeInvoice::STATUS_PARTIAL])
                    ->get()
                    ->sum(fn (FeeInvoice $invoice) => $invoice->balance),
            ],
            'recent_notices' => Notice::query()
                ->orderByDesc('published_at')
                ->limit(5)
                ->get(['id', 'title', 'category', 'audience', 'published_at']),
            'upcoming_events' => Event::query()
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->limit(5)
                ->get(['id', 'title', 'starts_at', 'location', 'color']),
        ];
    }

    /**
     * Data for the teacher portal dashboard.
     *
     * @return array<string, mixed>
     */
    public function teacher(User $user): array
    {
        $teacher = $user->teacher;
        $sessionId = AcademicSession::query()->active()->value('id');

        $assignments = $teacher
            ? $teacher->classTeachers()
                ->with(['class:id,name', 'section:id,name,class_id'])
                ->get()
                ->map(fn ($assignment) => [
                    'class' => $assignment->class?->name,
                    'section' => $assignment->section?->name,
                    'is_class_teacher' => $assignment->is_class_teacher,
                    'students' => Student::query()
                        ->where('class_id', $assignment->class_id)
                        ->where('section_id', $assignment->section_id)
                        ->active()
                        ->count(),
                ])
            : collect();

        return [
            'assignments' => $assignments,
            'today' => now()->format('Y-m-d'),
            'recent_notices' => Notice::query()
                ->whereIn('audience', ['all', 'teachers'])
                ->where('published_at', '<=', now())
                ->orderByDesc('published_at')
                ->limit(5)
                ->get(['id', 'title', 'body', 'published_at']),
            'upcoming_events' => Event::query()
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->limit(4)
                ->get(['id', 'title', 'starts_at', 'location']),
        ];
    }

    /**
     * Data for the student portal dashboard.
     *
     * @return array<string, mixed>
     */
    public function student(User $user): array
    {
        $student = $user->student;
        if ($student === null) {
            return ['student' => null];
        }

        $student->load(['class:id,name', 'section:id,name', 'session:id,name']);

        $presentDays = StudentAttendance::query()
            ->where('student_id', $student->id)
            ->where('status', StudentAttendance::PRESENT)
            ->count();
        $totalDays = StudentAttendance::query()->where('student_id', $student->id)->count();
        $attendanceRate = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : null;

        $latestExam = Exam::query()
            ->where('status', 'published')
            ->orderByDesc('starts_at')
            ->first();
        $marksheet = $latestExam ? app(ResultService::class)->marksheet($student, $latestExam) : null;

        $balance = (float) $student->invoices()->get()->sum(fn (FeeInvoice $invoice) => $invoice->balance);

        $transport = $student->transport()
            ->with(['route:id,name', 'stop:id,name'])
            ->where('is_active', true)
            ->first();

        return [
            'student' => $student,
            'admission_no' => $student->admission_no,
            'attendance_rate' => $attendanceRate,
            'latest_exam' => $latestExam,
            'marksheet' => $marksheet,
            'fee_balance' => $balance,
            'transport' => $transport,
            'recent_notices' => Notice::query()
                ->whereIn('audience', ['all', 'students'])
                ->where('published_at', '<=', now())
                ->orderByDesc('published_at')
                ->limit(5)
                ->get(['id', 'title', 'body', 'published_at']),
            'upcoming_events' => Event::query()
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->limit(4)
                ->get(['id', 'title', 'starts_at', 'location', 'color']),
        ];
    }

    /**
     * Data for the parent portal dashboard.
     *
     * @return array<string, mixed>
     */
    public function parent(User $user): array
    {
        $guardian = $user->guardian;

        if ($guardian === null) {
            return ['children' => collect()];
        }

        $children = $guardian->students()
            ->with(['class:id,name', 'section:id,name'])
            ->get()
            ->map(function (Student $student) {
                $present = StudentAttendance::query()->where('student_id', $student->id)->where('status', 'present')->count();
                $total = StudentAttendance::query()->where('student_id', $student->id)->count();

                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'admission_no' => $student->admission_no,
                    'class' => $student->class?->name,
                    'section' => $student->section?->name,
                    'status' => $student->status,
                    'attendance_rate' => $total > 0 ? round(($present / $total) * 100, 1) : null,
                    'fee_balance' => (float) $student->invoices()->get()->sum(fn (FeeInvoice $invoice) => $invoice->balance),
                ];
            })->values();

        return [
            'children' => $children,
            'recent_notices' => Notice::query()
                ->whereIn('audience', ['all', 'parents'])
                ->where('published_at', '<=', now())
                ->orderByDesc('published_at')
                ->limit(5)
                ->get(['id', 'title', 'body', 'published_at']),
            'upcoming_events' => Event::query()
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->limit(4)
                ->get(['id', 'title', 'starts_at', 'location', 'color']),
        ];
    }
}
