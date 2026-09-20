<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TeacherRequest;
use App\Models\Teacher;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $query = Teacher::query()
            ->with('user:id,phone')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'LIKE', "%{$s}%")->orWhere('employee_no', 'LIKE', "%{$s}%"))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->orderByDesc('id');

        return $this->render('admin/Teachers/Index', [
            'teachers' => $this->paginated($query->paginate(15)->withQueryString()),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function store(TeacherRequest $request)
    {
        $teacher = Teacher::query()->create($request->validated());
        $this->successFlash('Teacher created successfully.');

        return redirect()->route('admin.teachers.index');
    }

    public function update(TeacherRequest $request, Teacher $teacher)
    {
        $teacher->update($request->validated());
        $this->successFlash('Teacher updated successfully.');

        return redirect()->route('admin.teachers.index');
    }

    public function destroy(Teacher $teacher)
    {
        $teacher->delete();
        $this->successFlash('Teacher deleted.');

        return back();
    }
}
