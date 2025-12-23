<?php

namespace Modules\Admin\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Admin\app\Http\Requests\CreatePlanRequest;
use Modules\Admin\app\Http\Requests\UpdatePlanRequest;
use Modules\Admin\app\Services\Contracts\PlanServiceInterface;

class AdminPlanController extends Controller
{
    public function __construct(
        protected PlanServiceInterface $planService
    ) {}

    public function index(Request $request): Response
    {
        $type = $request->get('type', 'retailer');
        $search = $request->get('search');

        $plans = $this->planService->getPlans($type, $search);

        return Inertia::render('Admin/Plans', [
            'plans' => $plans,
            'currentType' => $type,
        ]);
    }

    public function store(CreatePlanRequest $request): RedirectResponse
    {
        $this->planService->createPlan($request->validated());

        return redirect()->back()->with('success', 'Plan created successfully.');
    }

    public function update(UpdatePlanRequest $request, int $id): RedirectResponse
    {
        $this->planService->updatePlan($id, $request->validated());

        return redirect()->back()->with('success', 'Plan updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->planService->deletePlan($id);

        return redirect()->back()->with('success', 'Plan deleted successfully.');
    }
}
