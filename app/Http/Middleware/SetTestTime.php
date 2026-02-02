<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTestTime
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Start session if not started (for cookie driver compatibility)
        if (!$request->hasSession() || !$request->session()->isStarted()) {
            $request->session()->start();
        }

        // Check if test time is set in session
        if ($request->session()->has('test_time_value')) {
            $testTime = $request->session()->get('test_time_value');

            // Set Carbon test time globally for this request
            Carbon::setTestNow(Carbon::parse($testTime));
        } else {
            // Reset to real time if no test time in session
            Carbon::setTestNow(null);
        }

        return $next($request);
    }
}
