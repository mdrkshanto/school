<?php

namespace App\Http\Controllers\Portal\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NoticeController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('portal/teacher/Notices', [
            'notices' => Notice::query()
                ->whereIn('audience', ['all', 'teachers'])
                ->where('published_at', '<=', now())
                ->when($request->category, fn ($q, $v) => $q->where('category', $v))
                ->orderByDesc('published_at')
                ->paginate(15)
                ->withQueryString(),
            'categories' => Notice::query()->distinct()->pluck('category'),
            'filters' => $request->only(['category']),
        ]);
    }
}
