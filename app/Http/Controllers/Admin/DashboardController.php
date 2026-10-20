<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminDashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     *
     * Dashboard reporting belongs to the service layer so the Blade view
     * only renders backend data and never carries demo/static metrics.
     */
    public function index(
        AdminDashboardService $dashboardService
    ): View {
        return view(
            'admin.dashboard.index',
            $dashboardService->build()
        );
    }
}
