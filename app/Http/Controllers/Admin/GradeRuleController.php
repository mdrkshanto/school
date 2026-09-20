<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GradeRuleRequest;
use App\Models\GradeRule;
use Illuminate\Http\Request;

class GradeRuleController extends Controller
{
    public function index(Request $request)
    {
        return $this->render('admin/Grades/Index', [
            'gradeRules' => $this->paginated(GradeRule::query()->orderBy('min_mark')->paginate(15)->withQueryString()),
        ]);
    }

    public function store(GradeRuleRequest $request)
    {
        GradeRule::query()->create($request->validated());
        $this->successFlash('Grade rule created.');

        return redirect()->route('admin.grades.index');
    }

    public function update(GradeRuleRequest $request, GradeRule $gradeRule)
    {
        $gradeRule->update($request->validated());
        $this->successFlash('Grade rule updated.');

        return redirect()->route('admin.grades.index');
    }

    public function destroy(GradeRule $gradeRule)
    {
        $gradeRule->delete();
        $this->successFlash('Grade rule deleted.');

        return back();
    }
}
