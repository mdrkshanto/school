<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExamRequest;
use App\Models\AcademicSession;
use App\Models\Exam;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index(Request $request)
    {
        $query = Exam::query()
            ->with('session:id,name')
            ->withCount('schedules')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'LIKE', "%{$s}%"))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->orderByDesc('starts_at');

        return $this->render('admin/Exams/Index', [
            'exams' => $this->paginated($query->paginate(15)->withQueryString()),
            'sessions' => AcademicSession::select('id', 'name')->get(),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function store(ExamRequest $request)
    {
        Exam::query()->create($request->validated());
        $this->successFlash('Exam created.');

        return redirect()->route('admin.exams.index');
    }

    public function update(ExamRequest $request, Exam $exam)
    {
        $exam->update($request->validated());
        $this->successFlash('Exam updated.');

        return redirect()->route('admin.exams.index');
    }

    public function destroy(Exam $exam)
    {
        $exam->delete();
        $this->successFlash('Exam and its schedules deleted.');

        return back();
    }
}
