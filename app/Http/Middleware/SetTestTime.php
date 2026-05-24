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
     * Sets Carbon::setTestNow() only while the application
     * is handling the request, then resets it before the
     * terminable middleware stack runs (e.g. session save,
     * queued jobs) to avoid polluting framework timestamps
     * such as session `last_activity`.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $hadTestTime = false;

        if ($request->hasSession() && $request->session()->has('test_time_value')) {
            $testTime = $request->session()->get('test_time_value');
            Carbon::setTestNow(Carbon::parse($testTime));
            $hadTestTime = true;
        }

        try {
            return $next($request);
        } finally {
            // Always restore real time after the controller finishes.
            // The terminable phase (session write, etc.) must run on real time
            // so framework timestamps like session last_activity stay valid.
            if ($hadTestTime) {
                Carbon::setTestNow(null);
            }
        }
    }
}
