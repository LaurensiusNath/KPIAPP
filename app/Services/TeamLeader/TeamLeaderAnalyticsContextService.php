<?php

declare(strict_types=1);

namespace App\Services\TeamLeader;

use App\Models\Division;
use App\Models\Period;
use App\Models\User;
use App\Services\Exceptions\UnauthorizedException;
use App\Services\PeriodService;
use Carbon\Carbon;

class TeamLeaderAnalyticsContextService
{
    public function getLeaderDivision(User $leader): ?Division
    {
        $leader->loadMissing('leading');

        return $leader->leading;
    }

    public function ensureUserInDivision(User $user, Division $division): void
    {
        if ($user->division_id !== $division->id) {
            throw new UnauthorizedException('User tidak berada dalam divisi Anda.');
        }
    }

    public function getActivePeriod(PeriodService $periodService): ?Period
    {
        return $periodService->getActivePeriod();
    }

    /**
     * @param array<int,int> $months
     * @return array<int, array{value:int,label:string}>
     */
    public function buildMonthOptions(Period $period, array $months): array
    {
        return collect($months)
            ->map(function (int $value) use ($period) {
                return [
                    'value' => $value,
                    'label' => Carbon::create($period->year, $value, 1)->translatedFormat('F'),
                ];
            })
            ->values()
            ->toArray();
    }

    /** @param array<int,int> $months */
    public function resolveMonth(array $months, ?int $requestedMonth): int
    {
        $requested = (int)($requestedMonth ?? 0);
        if ($requested !== 0 && in_array($requested, $months, true)) {
            return $requested;
        }

        $current = (int) now()->month;
        if (in_array($current, $months, true)) {
            return $current;
        }

        $last = end($months);
        if ($last !== false) {
            return (int) $last;
        }

        $first = reset($months);
        if ($first !== false) {
            return (int) $first;
        }

        return $current;
    }

    /** @param array<int,int> $validMonths */
    public function coerceMonth(int $month, array $validMonths): int
    {
        if (in_array($month, $validMonths, true)) {
            return $month;
        }

        return (int) ($validMonths[0] ?? now()->month);
    }
}
