<?php

namespace App\Http\Controllers\Portal\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Exam;
use App\Models\Result;
use App\Models\Student;
use App\Services\ResultService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ResultController extends Controller
{
    public function __construct(private readonly ResultService $results) {}

    public function index(Request $request)
    {
        $teacher = auth()->user()->teacher;

        $assignments = $teacher
            ? $teacher->classTeachers()->with(['class:id,name', 'section:id,name'])->get()
                ->map(fn ($a) => ['class_id' => $a->class_id, 'section_id' => $a->section_id, 'class' => $a->class->name, 'section' => $a->section?->name])
            : collect();

        $exams = $request->filled('class_id')
            ? Exam::query()
                ->where('status', '<>', 'draft')
                ->whereHas('schedules', fn ($q) => $q->where('class_id', $request->class_id))
                ->with('session:id,name')
                ->orderByDesc('starts_at')
                ->get()
                ->map(fn (Exam $e) => ['id' => $e->id, 'name' => $e->session?->name.' - '.$e->name])
            : collect();

        $rows = collect();

        if ($request->filled(['class_id', 'exam_id'])) {
            $exam = Exam::with('schedules.subject:id,name')->findOrFail($request->exam_id);
            $schedules = $exam->schedules->where('class_id', (int) $request->class_id);

            $students = Student::query()
                ->where('class_id', $request->class_id)
                ->when($request->filled('section_id'), fn ($q) => $q->where('section_id', $request->section_id))
                ->active()
                ->orderBy('name')
                ->get();

            $existing = Result::query()
                ->where('exam_id', $exam->id)
                ->whereIn('student_id', $students->pluck('id'))
                ->get()
                ->groupBy('student_id');

            $rows = $students->map(fn (Student $student) => [
                'student' => ['id' => $student->id, 'name' => $student->name, 'admission_no' => $student->admission_no],
                'subjects' => $schedules->map(function ($schedule) use ($existing, $student) {
                    $entry = ($existing[$student->id] ?? collect())->firstWhere('subject_id', $schedule->subject_id);

                    return [
                        'subject_id' => $schedule->subject_id,
                        'name' => $schedule->subject->name,
                        'full_marks' => (float) $schedule->full_marks,
                        'pass_marks' => (float) $schedule->pass_marks,
                        'obtained_marks' => $entry?->obtained_marks,
                        'is_absent' => (bool) $entry?->is_absent,
                        'grade' => $entry?->grade,
                        'grade_point' => $entry?->grade_point,
                    ];
                })->values(),
            ])->values();
        }

        return Inertia::render('portal/teacher/Results', [
            'assignments' => $assignments,
            'exams' => $exams,
            'rows' => $rows,
            'filters' => $request->only(['class_id', 'section_id', 'exam_id']),
            'sessionName' => AcademicSession::query()->active()->value('name'),
        ]);
    }

    public function save(Request $request)
    {
        $request->validate([
            'exam_id' => ['required', 'exists:exams,id'],
            'class_id' => ['required', 'integer'],
            'entries' => ['required', 'array'],
            'entries.*.student_id' => ['required', 'exists:students,id'],
            'entries.*.subject_id' => ['required', 'exists:subjects,id'],
            'entries.*.obtained_marks' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'entries.*.is_absent' => ['sometimes', 'boolean'],
        ]);

        $teacher = auth()->user()->teacher;
        $assignableClassIds = $teacher
            ? $teacher->classTeachers()->pluck('class_id')->map(fn ($id) => (int) $id)
            : collect();

        if (! $assignableClassIds->contains((int) $request->class_id)) {
            $this->errorFlash('You can only enter results for your assigned classes.');

            return back();
        }

        foreach ($request->input('entries') as $entry) {
            $student = Student::findOrFail($entry['student_id']);

            if ((int) $student->class_id !== (int) $request->class_id) {
                continue;
            }

            $result = Result::query()->updateOrCreate(
                ['exam_id' => $request->exam_id, 'student_id' => $student->id, 'subject_id' => $entry['subject_id']],
                ['class_id' => $student->class_id, 'section_id' => $student->section_id]
            );

            $isAbsent = (bool) ($entry['is_absent'] ?? false);

            if ($isAbsent || $entry['obtained_marks'] === null) {
                $result->update(['obtained_marks' => null, 'is_absent' => $isAbsent, 'grade' => null, 'grade_point' => null]);

                continue;
            }

            $grade = $this->results->gradeFor((float) $entry['obtained_marks']);

            $result->update([
                'obtained_marks' => $entry['obtained_marks'],
                'is_absent' => false,
                'grade' => $grade['name'] ?? null,
                'grade_point' => $grade['point'] ?? null,
            ]);
        }

        $this->successFlash('Results saved.');

        return back();
    }

    public function marksheet(Student $student, Exam $exam)
    {
        $teacher = auth()->user()->teacher;
        $assignableClassIds = $teacher ? $teacher->classTeachers()->pluck('class_id')->map(fn ($id) => (int) $id) : collect();

        if (! $assignableClassIds->contains((int) $student->class_id)) {
            abort(403, 'You can only view results for your assigned classes.');
        }

        return Inertia::render('portal/teacher/Marksheet', [
            'student' => $student->load('class:id,name', 'section:id,name'),
            'exam' => $exam->load('session:id,name'),
            'sheet' => $this->results->marksheet($student, $exam),
        ]);
    }
}
