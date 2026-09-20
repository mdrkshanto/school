<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GuardianRequest;
use App\Models\Guardian;
use Illuminate\Http\Request;

class GuardianController extends Controller
{
    public function index(Request $request)
    {
        $query = Guardian::query()
            ->when($request->search, fn ($q, $s) => $q->where('name', 'LIKE', "%{$s}%")->orWhere('email', 'LIKE', "%{$s}%"))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->orderByDesc('id');

        return $this->render('admin/Guardians/Index', [
            'guardians' => $this->paginated($query->paginate(15)->withQueryString()),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function store(GuardianRequest $request)
    {
        $guardian = Guardian::query()->create($request->except('student_ids'));

        if ($request->has('student_ids')) {
            foreach ($request->input('student_ids', []) as $studentId) {
                $guardian->students()->attach($studentId, ['relation' => 'parent']);
            }
        }

        $this->successFlash('Parent/Guardian created successfully.');

        return redirect()->route('admin.guardians.index');
    }

    public function update(GuardianRequest $request, Guardian $guardian)
    {
        $guardian->update($request->except('student_ids'));

        if ($request->has('student_ids')) {
            $syncData = array_fill_keys($request->input('student_ids', []), ['relation' => 'parent']);
            $guardian->students()->sync($syncData);
        }

        $this->successFlash('Parent/Guardian updated successfully.');

        return redirect()->route('admin.guardians.index');
    }

    public function destroy(Guardian $guardian)
    {
        $guardian->students()->detach();
        $guardian->delete();
        $this->successFlash('Parent/Guardian deleted.');

        return back();
    }
}
