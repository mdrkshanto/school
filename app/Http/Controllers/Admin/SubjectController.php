<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SubjectRequest;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Subject::query()
            ->with('classes:id,name')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'LIKE', "%{$s}%"))
            ->orderByDesc('id');

        return $this->render('admin/Subjects/Index', [
            'subjects' => $this->paginated($query->paginate(15)->withQueryString()),
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(SubjectRequest $request)
    {
        $subject = Subject::query()->create($request->only(['name', 'code', 'description', 'status']));
        if ($request->has('class_ids')) {
            $subject->classes()->sync($request->input('class_ids', []));
        }
        $this->successFlash('Subject created.');

        return redirect()->route('admin.subjects.index');
    }

    public function update(SubjectRequest $request, Subject $subject)
    {
        $subject->update($request->only(['name', 'code', 'description', 'status']));
        if ($request->has('class_ids')) {
            $subject->classes()->sync($request->input('class_ids', []));
        }
        $this->successFlash('Subject updated.');

        return redirect()->route('admin.subjects.index');
    }

    public function destroy(Subject $subject)
    {
        $subject->classes()->detach();
        $subject->delete();
        $this->successFlash('Subject deleted.');

        return back();
    }
}
