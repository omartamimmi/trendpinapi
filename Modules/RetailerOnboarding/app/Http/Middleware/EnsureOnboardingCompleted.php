<?php

namespace Modules\RetailerOnboarding\app\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingCompleted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Only check for retailer users
        if (!$user || !$user->hasRole('retailer')) {
            return $next($request);
        }

        // Allow access to onboarding routes
        if ($request->is('retailer/onboarding*') || $request->is('retailer/logout')) {
            return $next($request);
        }

        // Check if user has onboarding that requires completion
        $onboarding = $user->retailerOnboarding;

        if ($onboarding && $onboarding->requires_completion && $onboarding->status !== 'completed') {
            // Redirect to onboarding page
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Please complete your onboarding process.',
                    'redirect' => '/retailer/onboarding',
                ], 403);
            }

            return redirect('/retailer/onboarding')->with('warning', 'Please complete your onboarding to continue.');
        }

        return $next($request);
    }
}
