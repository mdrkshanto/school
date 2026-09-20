<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AttendanceRequest;
use App\Models\AcademicSession;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $sessionId = $request->session_id ?? AcademicSession::query()->active()->value('id');
        $students = collect();

        if ($request->filled(['class_id'])) {
            $students = Student::query()
                ->where('class_id', $request->class_id)
                ->when($request->section_id, fn ($q) => $q->where('section_id', $request->section_id))
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

        return Inertia::render('admin/Attendance/Index', [
            'students' => $students,
            'sessions' => AcademicSession::select('id', 'name')->get(),
            'classes' => SchoolClass::select('id', 'name')->orderBy('name')->get(),
            'sections' => Section::select('id', 'name', 'class_id')->get(),
            'filters' => $request->only(['session_id', 'class_id', 'section_id', 'date']),
            'attendanceDate' => $request->date ?? now()->toDateString(),
        ]);
    }

    public function mark(AttendanceRequest $request)
    {
        $created = 0;

        foreach ($request->input('records', []) as $record) {
            $exists = StudentAttendance::query()
                ->where('student_id', $record['student_id'])
                ->where('date', $request->date)
                ->where('session_id', $request->session_id)
                ->first();

            if ($exists) {
                $exists->update($record);
            } else {
                StudentAttendance::query()->create([
                    'session_id' => $request->session_id,
                    'class_id' => $request->class_id,
                    'section_id' => $request->section_id,
                    'student_id' => $record['student_id'],
                    'date' => $request->date,
                    'status' => $record['status'],
                    'remark' => $record['remark'] ?? null,
                ]);
                $created++;
            }
        }

        $this->successFlash("Attendance saved. {$created} new records created.");

        return back();
    }

    public function records(Request $request)
    {
        $query = StudentAttendance::query()
            ->with('student:id,name,admission_no', 'class:id,name')
            ->when($request->class_id, fn ($q) => $q->where('class_id', $request->class_id))
            ->when($request->date, fn ($q) => $q->where('date', $request->date))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('date');

        return Inertia::render('admin/Attendance/Records', [
            'records' => $this->paginated($query->paginate(15)->withQueryString()),
            'classes' => SchoolClass::select('id', 'name')->get(),
            'filters' => $request->only(['class_id', 'date', 'status']),
        ]);
    }

    public function teacherIndex(Request $request)
    {
        $teachers = Teacher::query()->with('user:id,name,email')->where('status', 'active')->get()->map(fn (Teacher $t) => [
            'id' => $t->id,
            'name' => $t->name,
            'employee_no' => $t->employee_no,
            'current_status' => TeacherAttendance::query()
                ->where('teacher_id', $t->id)
                ->where('date', $request->date ?? now()->toDateString())
                ->value('status') ?? 'present',
        ]);

        return Inertia::render('admin/Attendance/TeacherIndex', [
            'teachers' => $teachers,
            'attendanceDate' => $request->date ?? now()->toDateString(),
        ]);
    }

    public function teacherMark(Request $request)
    {
        $request->validate([
            'date' => ['required', 'date'],
            'records' => ['required', 'array'],
            'records.*.teacher_id' => ['required', 'exists:teachers,id'],
            'records.*.status' => ['required', 'in:present,absent,late,leave'],
        ]);

        foreach ($request->input('records') as $record) {
            TeacherAttendance::query()->updateOrCreate(
                ['teacher_id' => $record['teacher_id'], 'date' => $request->date],
                ['status' => $record['status'], 'remark' => $record['remark'] ?? null]
            );
        }

        $this->successFlash('Teacher attendance saved.');

        return back();
    }
}
