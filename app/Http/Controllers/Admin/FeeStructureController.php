<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FeeStructureRequest;
use App\Models\AcademicSession;
use App\Models\FeeStructure;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class FeeStructureController extends Controller
{
    public function index(Request $request)
    {
        $query = FeeStructure::query()
            ->with(['session:id,name', 'class:id,name'])
            ->when($request->search, fn ($q, $s) => $q->where('name', 'LIKE', "%{$s}%"))
            ->when($request->session_id, fn ($q, $v) => $q->where('session_id', $v))
            ->orderByDesc('id');

        return $this->render('admin/Fees/Index', [
            'structures' => $this->paginated($query->paginate(15)->withQueryString()),
            'sessions' => AcademicSession::select('id', 'name')->get(),
            'classes' => SchoolClass::select('id', 'name')->get(),
            'filters' => $request->only(['search', 'session_id']),
        ]);
    }

    public function store(FeeStructureRequest $request)
    {
        FeeStructure::query()->create($request->validated());
        $this->successFlash('Fee structure created.');

        return redirect()->route('admin.fees.index');
    }

    public function update(FeeStructureRequest $request, FeeStructure $feeStructure)
    {
        $feeStructure->update($request->validated());
        $this->successFlash('Fee structure updated.');

        return redirect()->route('admin.fees.index');
    }

    public function destroy(FeeStructure $feeStructure)
    {
        $feeStructure->delete();
        $this->successFlash('Fee structure deleted.');

        return back();
    }
}
