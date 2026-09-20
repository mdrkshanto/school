<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SchoolClassRequest;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function index(Request $request)
    {
        $query = SchoolClass::query()
            ->with(['sections:id,name,class_id'])
            ->when($request->search, fn ($q, $s) => $q->where('name', 'LIKE', "%{$s}%"))
            ->orderByDesc('numeric_value');

        return $this->render('admin/Classes/Index', [
            'classes' => $this->paginated($query->paginate(15)->withQueryString()),
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(SchoolClassRequest $request)
    {
        $class = SchoolClass::query()->create($request->only(['name', 'numeric_value', 'description', 'status']));
        if ($request->has('subject_ids')) {
            $class->subjects()->sync($request->input('subject_ids', []));
        }
        $this->successFlash('Class created.');

        return redirect()->route('admin.classes.index');
    }

    public function update(SchoolClassRequest $request, SchoolClass $class)
    {
        $class->update($request->only(['name', 'numeric_value', 'description', 'status']));
        if ($request->has('subject_ids')) {
            $class->subjects()->sync($request->input('subject_ids', []));
        }
        $this->successFlash('Class updated.');

        return redirect()->route('admin.classes.index');
    }

    public function destroy(SchoolClass $class)
    {
        $class->subjects()->detach();
        $class->delete();
        $this->successFlash('Class deleted.');

        return back();
    }
}
