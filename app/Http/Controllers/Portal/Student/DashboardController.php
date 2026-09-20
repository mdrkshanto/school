<?php

namespace App\Http\Controllers\Portal\Student;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboard) {}

    public function index()
    {
        return Inertia::render('portal/student/Dashboard', [
            'data' => $this->dashboard->student(auth()->user()),
        ]);
    }
}
