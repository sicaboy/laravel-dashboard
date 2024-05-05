<?php

namespace Sicaboy\LaravelDashboard\Http\Controllers;

use Sicaboy\LaravelDashboard\Repositories\DashboardRepository;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DashboardController extends Controller
{
    public function __construct(DashboardRepository $dashboardRepository)
    {
        $this->dashboardRepository = $dashboardRepository;
    }

    public function getDashboardMeta(Request $request, $id)
    {
        return $this->dashboardRepository->getDashboards($id);
    }

    public function getDashboard(Request $request, $id)
    {
        return $this->dashboardRepository->getDashboards($id, $request);
    }
}
