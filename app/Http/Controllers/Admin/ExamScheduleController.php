<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExamScheduleRequest;
use App\Models\Exam;
use App\Models\ExamSchedule;

class ExamScheduleController extends Controller
{
    public function store(ExamScheduleRequest $request)
    {
        Exam::findOrFail($request->exam_id);
        ExamSchedule::query()->create($request->validated());
        $this->successFlash('Schedule added.');

        return back();
    }

    public function update(ExamScheduleRequest $request, ExamSchedule $schedule)
    {
        $schedule->update($request->validated());
        $this->successFlash('Schedule updated.');

        return back();
    }

    public function destroy(ExamSchedule $schedule)
    {
        $schedule->delete();
        $this->successFlash('Schedule removed.');

        return back();
    }
}
