<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AcademicSessionRequest;
use App\Models\AcademicSession;
use Illuminate\Http\Request;

class AcademicSessionController extends Controller
{
    public function index(Request $request)
    {
        $query = AcademicSession::query()
            ->withCount('students')
            ->orderByDesc('starts_at');

        return $this->render('admin/Sessions/Index', [
            'sessions' => $this->paginated($query->paginate(15)->withQueryString()),
            'filters' => $request->only([]),
        ]);
    }

    public function store(AcademicSessionRequest $request)
    {
        if ($request->is_active) {
            AcademicSession::query()->where('id', '!=', 0)->update(['is_active' => false]);
        }
        AcademicSession::query()->create($request->validated());
        $this->successFlash('Session created.');

        return redirect()->route('admin.sessions.index');
    }

    public function update(AcademicSessionRequest $request, AcademicSession $session)
    {
        if ($request->is_active) {
            AcademicSession::query()->where('id', '!=', $session->id)->update(['is_active' => false]);
        }
        $session->update($request->validated());
        $this->successFlash('Session updated.');

        return redirect()->route('admin.sessions.index');
    }

    public function destroy(AcademicSession $session)
    {
        $session->delete();
        $this->successFlash('Session deleted.');

        return back();
    }
}
