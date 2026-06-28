<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveRole
{
    public function handle(Request $request, Closure $next, string $requiredRole): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($requiredRole === 'admin') {
            $isAdmin = $user->roles()->where('name', 'admin')->exists();
            if (! $isAdmin) {
                return response()->json([
                    'message' => 'Forbidden. Admin access required.',
                ], 403);
            }
            return $next($request);
        }

        if ($user->active_role !== $requiredRole) {
            return response()->json([
                'message' => "Forbidden. Your active role is '{$user->active_role}', but this action requires '{$requiredRole}'.",
                'active_role' => $user->active_role,
            ], 403);
        }
   
        $ownsRole = $user->roles()->where('name', $requiredRole)->exists();
        if (! $ownsRole) {
            return response()->json([
                'message' => "Forbidden. You do not own the '{$requiredRole}' role.",
            ], 403);
        }

        return $next($request);
    }
}
