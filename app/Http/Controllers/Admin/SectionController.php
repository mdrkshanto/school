<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SectionRequest;
use App\Models\Section;

class SectionController extends Controller
{
    public function store(SectionRequest $request)
    {
        Section::query()->create($request->validated());
        $this->successFlash('Section created.');

        return back();
    }

    public function update(SectionRequest $request, Section $section)
    {
        $section->update($request->validated());
        $this->successFlash('Section updated.');

        return back();
    }

    public function destroy(Section $section)
    {
        $section->delete();
        $this->successFlash('Section deleted.');

        return back();
    }
}
