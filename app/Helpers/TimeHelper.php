<?php

namespace App\Helpers;

use Carbon\Carbon;

class TimeHelper
{
    /**
     * Get current time, with test time override support
     * This replaces Carbon::now() for business logic
     */
    public static function now(): Carbon
    {
        // Check if test time is set in session
        if (session()->has('test_time_value')) {
            return Carbon::parse(session('test_time_value'));
        }

        // Return real current time
        return Carbon::now();
    }

    /**
     * Check if test mode is active
     */
    public static function isTestMode(): bool
    {
        return session()->has('test_time_value');
    }

    /**
     * Get test time description
     */
    public static function getTestTimeLabel(): ?string
    {
        if (!self::isTestMode()) {
            return null;
        }

        return Carbon::parse(session('test_time_value'))->format('d M Y H:i');
    }
}
