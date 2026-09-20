<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ResultEntryRequest;
use App\Models\AcademicSession;
use App\Models\Exam;
use App\Models\GradeRule;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Services\PdfService;
use App\Services\ResultService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ResultController extends Controller
{
    public function __construct(
        private readonly ResultService $results,
        private readonly PdfService $pdf,
    ) {}

    public function index(Request $request)
    {
        $query = Student::query()
            ->with(['class:id,name', 'section:id,name', 'session:id,name'])
            ->when($request->search, fn ($q, $s) => $q->where('name', 'LIKE', "%{$s}%")->orWhere('admission_no', 'LIKE', "%{$s}%"))
            ->when($request->class_id, fn ($q, $v) => $q->where('class_id', $v))
            ->when($request->exam_id, function ($q, $v) {
                $q->whereHas('results', fn ($rq) => $rq->where('exam_id', $v));
            })
            ->when($request->section_id, fn ($q, $v) => $q->where('section_id', $v))
            ->orderByDesc('id');

        return $this->render('admin/Results/Index', [
            'students' => $this->paginated($query->paginate(15)->withQueryString()),
            'exams' => Exam::with('session:id,name')->get()->map(fn (Exam $e) => ['id' => $e->id, 'name' => $e->session?->name.' - '.$e->name]),
            'classes' => SchoolClass::select('id', 'name')->get(),
            'sections' => Section::select('id', 'name', 'class_id')->get(),
            'filters' => $request->only(['search', 'exam_id', 'class_id', 'section_id']),
        ]);
    }

    public function entry(Request $request)
    {
        $exam = Exam::with('session:id,name')->findOrFail($request->integer('exam_id'));

        $schedules = $exam->schedules()
            ->where('class_id', $request->integer('class_id'))
            ->with('subject:id,name,code')
            ->get();

        $students = Student::query()
            ->where('class_id', $request->integer('class_id'))
            ->when($request->filled('section_id'), fn ($q) => $q->where('section_id', $request->section_id))
            ->where('session_id', $exam->session_id)
            ->active()
            ->orderBy('name')
            ->get();

        $existing = DB::table('results')
            ->where('exam_id', $exam->id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get(['student_id', 'subject_id', 'obtained_marks', 'is_absent', 'grade', 'grade_point'])
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

        return Inertia::render('admin/Results/Entry', [
            'exam' => $exam,
            'class' => SchoolClass::find($request->integer('class_id')),
            'schedules' => $schedules->map(fn ($s) => [
                'id' => $s->id,
                'subject_id' => $s->subject_id,
                'name' => $s->subject->name,
                'full_marks' => (float) $s->full_marks,
                'pass_marks' => (float) $s->pass_marks,
            ]),
            'rows' => $rows,
            'activeSession' => AcademicSession::query()->active()->value('name'),
        ]);
    }

    public function saveEntry(ResultEntryRequest $request)
    {
        $grades = GradeRule::query()->orderBy('min_mark', 'desc')->get()
            ->mapWithKeys(fn ($rule) => [
                $rule->grade_point => [
                    'name' => $rule->name,
                    'point' => (string) $rule->grade_point,
                    'min' => (float) $rule->min_mark,
                    'max' => (float) $rule->max_mark,
                ],
            ]);

        foreach ($request->input('entries', []) as $entry) {
            $examId = (int) $entry['exam_id'];
            $studentId = (int) $entry['student_id'];
            $subjectId = (int) $entry['subject_id'];
            $classId = (int) $entry['class_id'];

            $result = Result::query()->updateOrCreate(
                ['exam_id' => $examId, 'student_id' => $studentId, 'subject_id' => $subjectId],
                ['class_id' => $classId, 'section_id' => $entry['section_id'] ?: null]
            );

            $isAbsent = (bool) ($entry['is_absent'] ?? false);
            $marks = $isAbsent ? null : $entry['obtained_marks'];

            if (is_numeric($marks) && $marks !== null) {
                $gradeInfo = $grades->first(
                    fn (array $info, $point) => (float) $marks <= (float) $info['max']
                        && (float) $marks >= (float) $info['min']
                );

                $result->update([
                    'obtained_marks' => (float) $marks,
                    'is_absent' => false,
                    'grade' => $gradeInfo['name'] ?? null,
                    'grade_point' => $gradeInfo ? (string) $gradeInfo['point'] : null,
                ]);
            } else {
                $result->update([
                    'obtained_marks' => null,
                    'is_absent' => $isAbsent,
                    'grade' => null,
                    'grade_point' => null,
                ]);
            }
        }

        $this->successFlash('Results saved.');

        return redirect()->route('admin.results.index');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'exam_id' => ['required', 'exists:exams,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'section_id' => ['nullable', 'exists:sections,id'],
        ]);

        $exam = Exam::findOrFail($request->exam_id);
        $class = SchoolClass::findOrFail($request->class_id);

        $count = $this->results->generateResults($exam, $class, $request->input('section_id'));

        $this->successFlash($count > 0 ? "Generated {$count} result slots." : 'Result slots already exist for every student.');

        return redirect()->route('admin.results.index');
    }

    public function marksheet(Student $student, Exam $exam)
    {
        $sheet = $this->results->marksheet($student, $exam);

        return Inertia::render('admin/Results/Marksheet', [
            'student' => $student->load('class:id,name', 'section:id,name'),
            'exam' => $exam->load('session:id,name'),
            'sheet' => $sheet,
        ]);
    }

    public function marksheetPdf(Request $request, Student $student, Exam $exam)
    {
        $pdf = $this->pdf->pdfString($this->pdf->marksheetHtml($student, $exam));

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "inline; filename=\"{$student->name}-{$exam->name}-marksheet.pdf\"");
    }
}
