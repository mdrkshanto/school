<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NoticeRequest;
use App\Models\Notice;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function index(Request $request)
    {
        $query = Notice::query()
            ->with('author:id,name')
            ->when($request->search, fn ($q, $s) => $q->where('title', 'LIKE', "%{$s}%"))
            ->when($request->audience, fn ($q, $v) => $q->where('audience', $v))
            ->orderByDesc('published_at');

        return $this->render('admin/Notices/Index', [
            'notices' => $this->paginated($query->paginate(15)->withQueryString()),
            'filters' => $request->only(['search', 'audience']),
        ]);
    }

    public function store(NoticeRequest $request)
    {
        Notice::query()->create([
            ...$request->validated(),
            'user_id' => auth()->id(),
            'published_at' => $request->date('published_at') ?? now(),
        ]);
        $this->successFlash('Notice published.');

        return redirect()->route('admin.notices.index');
    }

    public function update(NoticeRequest $request, Notice $notice)
    {
        $notice->update($request->validated());
        $this->successFlash('Notice updated.');

        return redirect()->route('admin.notices.index');
    }

    public function destroy(Notice $notice)
    {
        $notice->delete();
        $this->successFlash('Notice deleted.');

        return back();
    }
}
