<?php

namespace App\Http\Controllers\Portal\Teacher;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboard) {}

    public function index()
    {
        return Inertia::render('portal/teacher/Dashboard', [
            'data' => $this->dashboard->teacher(auth()->user()),
        ]);
    }
}
