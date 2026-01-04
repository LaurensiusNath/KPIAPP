<?php

namespace App\Services\Admin;

use App\Models\Appraisal;
use App\Models\Division;
use App\Models\Period;
use App\Models\User;
use App\Services\AppraisalService;
use App\Services\PeriodService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AdminAppraisalService
{
    public function getPeriodsForIndex(): Collection
    {
        return Period::query()
            ->orderByDesc('is_active')
            ->orderByDesc('year')
            ->orderByDesc('semester')
            ->get();
    }

    public function getUsersForIndexPaginated(int $perPage = 25): LengthAwarePaginator
    {
        return User::query()
            ->where('role', 'user')
            ->with('division')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getAppraisalsForPeriod(?int $periodId): Collection
    {
        if (!$periodId) {
            return collect();
        }

        return Appraisal::query()
            ->where('period_id', $periodId)
            ->get()
            ->keyBy('user_id');
    }

    public function getPeriodsForDivisionPicker(): Collection
    {
        return Period::query()
            ->orderByDesc('is_active')
            ->orderBy('year', 'desc')
            ->orderBy('semester', 'desc')
            ->get();
    }

    public function getActivePeriod(PeriodService $periodService): ?Period
    {
        return $periodService->getActivePeriod();
    }

    /**
     * Returns an array shaped exactly like the current Divisions appraisal view expects.
     */
    public function getDivisionRowsForPeriod(Period $period, AppraisalService $appraisalService): array
    {
        $allDivisions = Division::query()->orderBy('name')->get();

        return $allDivisions
            ->map(function (Division $division) use ($appraisalService, $period) {
                $summary = $appraisalService->getDivisionAppraisalSummary($division, $period);

                return [
                    'id' => $division->id,
                    'name' => $division->name,
                    'staff_count' => $summary['staff_count'],
                    'overall_average' => $summary['overall_average'],
                ];
            })
            ->toArray();
    }
}
