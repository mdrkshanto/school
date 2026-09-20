<?php

namespace App\Http\Controllers\Portal\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Result;
use App\Services\ResultService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ResultController extends Controller
{
    public function __construct(private readonly ResultService $results) {}

    public function index()
    {
        $student = auth()->user()->student;

        if ($student === null) {
            abort(403, 'No student profile linked to this account.');
        }

        $exams = Exam::query()
            ->where('status', 'published')
            ->whereHas('schedules', fn ($q) => $q->where('class_id', $student->class_id))
            ->with('session:id,name')
            ->orderByDesc('starts_at')
            ->get();

        $available = $exams->filter(function (Exam $exam) use ($student) {
            return Result::query()->where('exam_id', $exam->id)->where('student_id', $student->id)->exists();
        })->values();

        return Inertia::render('portal/student/Results', [
            'exams' => $available->map(fn (Exam $exam) => [
                'id' => $exam->id,
                'name' => $exam->session?->name.' - '.$exam->name,
                'starts_at' => $exam->starts_at,
            ]),
        ]);
    }

    public function show(Request $request, Exam $exam)
    {
        $student = auth()->user()->student;

        if ($student === null) {
            abort(403);
        }

        abort_unless((int) $student->class_id === (int) $exam->class_id ?? false, 403);

        $exists = Result::query()->where('exam_id', $exam->id)->where('student_id', $student->id)->exists();
        abort_unless($exists, 404, 'Results not yet published for this exam.');

        $sheet = $this->results->marksheet($student, $exam);

        return Inertia::render('portal/student/Marksheet', [
            'student' => $student->load('class:id,name', 'section:id,name'),
            'exam' => $exam->load('session:id,name'),
            'sheet' => $sheet,
        ]);
    }
}
