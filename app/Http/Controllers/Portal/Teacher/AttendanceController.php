<?php

namespace App\Http\Controllers\Portal\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;

class AttendanceController extends Controller
{
    private function teacher(): ?Teacher
    {
        return auth()->user()->teacher;
    }

    /**
     * Assignments (classes/sections) this teacher can mark attendance for.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function assignments(): Collection
    {
        $teacher = $this->teacher();

        if ($teacher === null) {
            return collect();
        }

        return $teacher->classTeachers()
            ->with(['class:id,name', 'section:id,name'])
            ->get()
            ->map(fn ($assignment) => [
                'class_id' => $assignment->class_id,
                'section_id' => $assignment->section_id,
                'class' => $assignment->class->name,
                'section' => $assignment->section?->name,
                'is_class_teacher' => $assignment->is_class_teacher,
            ]);
    }

    public function index(Request $request)
    {
        $assignments = $this->assignments();
        $students = collect();

        if ($request->filled('class_id') && $assignments->contains('class_id', (int) $request->class_id)) {
            $students = Student::query()
                ->where('class_id', $request->class_id)
                ->when($request->filled('section_id'), fn ($q) => $q->where('section_id', $request->section_id))
                ->active()
                ->orderBy('name')
                ->get()
                ->map(fn (Student $s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'admission_no' => $s->admission_no,
                    'current_status' => StudentAttendance::query()
                        ->where('student_id', $s->id)
                        ->where('date', $request->date ?? now()->toDateString())
                        ->value('status') ?? 'present',
                ]);
        }

        return Inertia::render('portal/teacher/Attendance', [
            'assignments' => $assignments,
            'students' => $students,
            'date' => $request->date ?? now()->toDateString(),
            'sessionName' => AcademicSession::query()->active()->value('name'),
        ]);
    }

    public function mark(Request $request)
    {
        $request->validate([
            'class_id' => ['required', 'integer'],
            'section_id' => ['nullable', 'integer'],
            'date' => ['required', 'date'],
            'records' => ['required', 'array'],
            'records.*.student_id' => ['required', 'exists:students,id'],
            'records.*.status' => ['required', 'in:present,absent,late,leave'],
        ]);

        if (! $this->assignments()->contains('class_id', (int) $request->class_id)) {
            $this->errorFlash('You are only allowed to mark attendance for your assigned classes.');

            return back();
        }

        $sessionId = AcademicSession::query()->active()->value('id');

        foreach ($request->input('records') as $record) {
            StudentAttendance::query()->updateOrCreate(
                ['student_id' => $record['student_id'], 'date' => $request->date],
                [
                    'session_id' => $sessionId,
                    'class_id' => $request->class_id,
                    'section_id' => $request->input('section_id'),
                    'status' => $record['status'],
                    'remark' => $record['remark'] ?? null,
                ]
            );
        }

        $this->successFlash('Attendance saved.');

        return back();
    }
}
