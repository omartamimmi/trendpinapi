<?php

namespace Modules\Admin\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Admin\app\Services\Contracts\DashboardServiceInterface;

class AdminDashboardController extends Controller
{
    public function __construct(
        protected DashboardServiceInterface $dashboardService
    ) {}

    public function index(): Response
    {
        $stats = $this->dashboardService->getStats();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
        ]);
    }
}
