<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user_role = Auth::user()->role ?? 'user';
        if (!in_array($user_role, $roles)) {
            $redirect_url = [
                'admin' => '/admin/dashboard',
                'team-leader' => '/team-leader/dashboard',
                'user' => '/user/dashboard',
            ];

            return redirect($redirect_url[$user_role] ?? '/');
        }
        return $next($request);
    }
}
