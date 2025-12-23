<?php

namespace Modules\Admin\app\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user('admin');

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('admin.login');
        }

        if (empty($roles)) {
            $roles = ['super_admin', 'admin'];
        }

        if (!$user->hasAnyRole($roles)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized. Insufficient permissions.'], 403);
            }
            abort(403, 'Unauthorized. Insufficient permissions.');
        }

        return $next($request);
    }
}
