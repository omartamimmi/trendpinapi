<?php

namespace Modules\Admin\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\RetailerOnboarding\app\Models\SubscriptionPayment;

class AdminPaymentController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->get('search');

        $query = SubscriptionPayment::with(['user', 'subscription.plan']);

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $payments = $query->latest()->paginate(20);

        return Inertia::render('Admin/Payments', [
            'payments' => $payments,
        ]);
    }
}
