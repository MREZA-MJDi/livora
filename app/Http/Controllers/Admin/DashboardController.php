<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the owner dashboard.
     *
     * The controller only handles request input and delegates
     * business/reporting calculations to the dashboard service.
     */
    public function index(
        Request $request,
        AdminDashboardService $dashboardService
    ): View {
        $months = (int) $request->input('range', 6);

        return view(
            'admin.dashboard.index',
            $dashboardService->build($months)
        );
    }
}
