<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StudentRequest;
use App\Models\AcademicSession;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function __construct(private readonly MediaService $media) {}

    public function index(Request $request)
    {
        $query = Student::query()
            ->with(['class:id,name', 'section:id,name', 'session:id,name'])
            ->when($request->search, fn ($q, $s) => $q->where('name', 'LIKE', "%{$s}%")->orWhere('admission_no', 'LIKE', "%{$s}%"))
            ->when($request->class_id, fn ($q, $v) => $q->where('class_id', $v))
            ->when($request->section_id, fn ($q, $v) => $q->where('section_id', $v))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->orderByDesc('id');

        $students = $this->paginated($query->paginate(15)->withQueryString());

        return $this->render('admin/Students/Index', [
            'students' => $students,
            'classes' => SchoolClass::select('id', 'name')->orderBy('name')->get(),
            'sections' => Section::with('class:id,name')->get()->map(fn (Section $s) => ['id' => $s->id, 'name' => $s->name, 'class_name' => $s->class?->name]),
            'sessions' => AcademicSession::select('id', 'name')->orderBy('id')->get(),
            'filters' => $request->only(['search', 'class_id', 'section_id', 'status']),
        ]);
    }

    public function store(StudentRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();
            $userId = null;

            if ($request->filled('email')) {
                $user = User::query()->firstOrCreate(
                    ['email' => $request->email],
                    ['name' => $request->name, 'password' => bcrypt($request->name), 'phone' => $request->phone, 'is_active' => true]
                );
                $user->assignRole('student');
                $userId = $user->id;
            }

            $student = Student::query()->create([
                ...$data,
                'user_id' => $userId,
                'session_id' => $data['session_id'] ?? AcademicSession::query()->active()->value('id'),
            ]);

            if ($request->hasFile('avatar')) {
                $path = $this->media->storeImage($request->file('avatar'), 'avatars/students');
                $student->update(['avatar' => $path]);
            }

            DB::commit();
            $this->successFlash('Student created successfully.');

            return redirect()->route('admin.students.index');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->errorFlash($e->getMessage());

            return back()->withInput();
        }
    }

    public function update(StudentRequest $request, Student $student)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();

            if ($request->filled('email')) {
                $user = $student->user;
                if ($user) {
                    $user->update(['name' => $data['name'], 'email' => $request->email, 'phone' => $request->phone]);
                }
            }

            $student->update($data);

            if ($request->hasFile('avatar')) {
                $this->media->deleteImage($student->avatar);
                $path = $this->media->storeImage($request->file('avatar'), 'avatars/students');
                $student->update(['avatar' => $path]);
            }

            DB::commit();
            $this->successFlash('Student updated successfully.');

            return redirect()->route('admin.students.index');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->errorFlash($e->getMessage());

            return back()->withInput();
        }
    }

    public function destroy(Student $student)
    {
        $this->media->deleteImage($student->avatar);
        $student->delete();
        $this->successFlash('Student deleted.');

        return back();
    }
}
