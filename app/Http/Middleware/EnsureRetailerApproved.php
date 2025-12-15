<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRetailerApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Allow if user is not a retailer (e.g., admin, customer)
        if (!$user || !$user->hasRole('retailer')) {
            return $next($request);
        }

        // Check retailer onboarding status
        $onboarding = $user->retailerOnboarding;

        // If no onboarding record, redirect to onboarding
        if (!$onboarding) {
            return redirect()->route('retailer.onboarding');
        }

        // If onboarding not completed OR not approved, redirect to onboarding page
        // The onboarding controller will handle showing the pending page or the form
        if ($onboarding->status !== 'completed' || $onboarding->approval_status !== 'approved') {
            // Allow access to onboarding routes
            if ($request->is('retailer/onboarding*')) {
                return $next($request);
            }
            // Otherwise redirect to onboarding (which will show pending page or form)
            return redirect()->route('retailer.onboarding');
        }

        return $next($request);
    }
}
