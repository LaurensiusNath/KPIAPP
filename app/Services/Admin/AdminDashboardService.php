<?php

namespace App\Services\Admin;

use App\Models\Division;
use App\Models\Period;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardService
{
    /**
     * Get monthly division performance for chart
     * Optimized: Uses single SQL query with Group By instead of nested loops
     */
    public function getMonthlyDivisionPerformance(): array
    {
        $activePeriod = $this->getActivePeriod();
        if (!$activePeriod) {
            return [];
        }

        $months = $activePeriod->semester === 1 ? range(1, 6) : range(7, 12);

        // Fetch raw data grouped by division and month in ONE query
        $stats = DB::table('kpi_values')
            ->join('kpis', 'kpi_values.kpi_id', '=', 'kpis.id')
            ->select(
                'kpi_values.division_id',
                'kpi_values.month',
                DB::raw('SUM(kpi_values.score * kpis.weight) as total_weighted_score'),
                DB::raw('SUM(kpis.weight) as total_weight')
            )
            ->where('kpi_values.period_id', $activePeriod->id)
            ->where('kpi_values.is_submitted', true)
            ->whereIn('kpi_values.month', $months)
            ->groupBy('kpi_values.division_id', 'kpi_values.month')
            ->get();

        $divisions = Division::orderBy('name')->get();

        $result = [];
        foreach ($divisions as $division) {
            $monthlyData = [];
            foreach ($months as $month) {
                $stat = $stats->where('division_id', $division->id)->where('month', $month)->first();

                $average = null;
                if ($stat && $stat->total_weight > 0) {
                    $average = round($stat->total_weighted_score / $stat->total_weight, 2);
                }

                $monthlyData[] = [
                    'month' => $month,
                    'label' => Carbon::create($activePeriod->year, $month, 1)->translatedFormat('M'),
                    'average' => $average,
                ];
            }

            $result[] = [
                'division_id' => $division->id,
                'division_name' => $division->name,
                'data' => $monthlyData,
            ];
        }

        return $result;
    }

    /**
     * Get monthly evaluation status per division
     * Optimized: Uses withCount to avoid N+1 queries
     */
    public function getMonthlyEvaluationStatus(): array
    {
        $activePeriod = $this->getActivePeriod();
        if (!$activePeriod) {
            return [];
        }

        $currentMonth = (int) Carbon::now()->month;

        $divisions = Division::with('leader')
            ->withCount([
                'users as total_staff' => function ($query) {
                    $query->where('role', 'user');
                },
                'users as evaluated_staff' => function ($query) use ($activePeriod, $currentMonth) {
                    $query->where('role', 'user')
                        ->whereHas('kpiValues', function ($q) use ($activePeriod, $currentMonth) {
                            $q->where('period_id', $activePeriod->id)
                                ->where('month', $currentMonth)
                                ->where('is_submitted', true);
                        });
                }
            ])
            ->orderBy('name')
            ->get();

        return $divisions->map(function ($division) {
            $percentage = $division->total_staff > 0
                ? round(($division->evaluated_staff / $division->total_staff) * 100, 1)
                : 0;

            return [
                'division_id' => $division->id,
                'division_name' => $division->name,
                'leader_name' => $division->leader?->name ?? 'Belum ada leader',
                'total_staff' => $division->total_staff,
                'evaluated_staff' => $division->evaluated_staff,
                'pending_staff' => $division->total_staff - $division->evaluated_staff,
                'percentage' => $percentage,
            ];
        })->toArray();
    }

    /**
     * Get appraisal status summary
     * Optimized: Uses single query with conditional aggregation
     */
    public function getAppraisalStatus(): array
    {
        $activePeriod = $this->getActivePeriod();
        if (!$activePeriod) {
            return [
                'pending_teamleader' => 0,
                'pending_hrd' => 0,
                'finalized' => 0,
                'total' => 0,
            ];
        }

        $stats = DB::table('appraisals')
            ->select(
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN teamleader_submitted_at IS NULL THEN 1 ELSE 0 END) as pending_teamleader'),
                DB::raw('SUM(CASE WHEN teamleader_submitted_at IS NOT NULL AND hrd_submitted_at IS NULL THEN 1 ELSE 0 END) as pending_hrd'),
                DB::raw('SUM(CASE WHEN is_finalized = true THEN 1 ELSE 0 END) as finalized')
            )
            ->where('period_id', $activePeriod->id)
            ->first();

        return [
            'pending_teamleader' => (int) ($stats->pending_teamleader ?? 0),
            'pending_hrd' => (int) ($stats->pending_hrd ?? 0),
            'finalized' => (int) ($stats->finalized ?? 0),
            'total' => (int) ($stats->total ?? 0),
        ];
    }

    /**
     * Get top performers for current month
     * Optimized: Calculates average in SQL, sorts and limits in database
     */
    public function getTopPerformers(int $limit = 3): array
    {
        $activePeriod = $this->getActivePeriod();
        if (!$activePeriod) {
            return [];
        }

        $currentMonth = (int) Carbon::now()->month;

        $topUsers = DB::table('users')
            ->join('kpi_values', 'users.id', '=', 'kpi_values.user_id')
            ->join('kpis', 'kpi_values.kpi_id', '=', 'kpis.id')
            ->leftJoin('divisions', 'users.division_id', '=', 'divisions.id')
            ->select(
                'users.id',
                'users.name',
                'divisions.name as division_name',
                DB::raw('SUM(kpi_values.score * kpis.weight) / NULLIF(SUM(kpis.weight), 0) as weighted_avg')
            )
            ->where('users.role', 'user')
            ->where('kpi_values.period_id', $activePeriod->id)
            ->where('kpi_values.month', $currentMonth)
            ->where('kpi_values.is_submitted', true)
            ->groupBy('users.id', 'users.name', 'divisions.name')
            ->orderByDesc('weighted_avg')
            ->limit($limit)
            ->get();

        return $topUsers->map(function ($user) {
            return [
                'user_id' => $user->id,
                'name' => $user->name,
                'division' => $user->division_name ?? '-',
                'score' => round($user->weighted_avg ?? 0, 2),
            ];
        })->toArray();
    }

    /**
     * Get division summary with statistics
     * Optimized: Uses aggregated queries and withCount to reduce database calls
     */
    public function getDivisionSummary(): array
    {
        $activePeriod = $this->getActivePeriod();
        if (!$activePeriod) {
            return [];
        }

        $currentMonth = (int) Carbon::now()->month;

        // Get KPI averages grouped by division in one query
        $kpiStats = DB::table('kpi_values')
            ->join('kpis', 'kpi_values.kpi_id', '=', 'kpis.id')
            ->select(
                'kpi_values.division_id',
                DB::raw('SUM(kpi_values.score * kpis.weight) as total_weighted'),
                DB::raw('SUM(kpis.weight) as total_weight')
            )
            ->where('kpi_values.period_id', $activePeriod->id)
            ->where('kpi_values.month', $currentMonth)
            ->where('kpi_values.is_submitted', true)
            ->groupBy('kpi_values.division_id')
            ->get()
            ->keyBy('division_id');

        // Get divisions with counts in one query
        $divisions = Division::with('leader')
            ->withCount([
                'users as staff_count' => fn($q) => $q->where('role', 'user'),
                'appraisals as appraisal_finalized' => fn($q) => $q->where('period_id', $activePeriod->id)->where('is_finalized', true),
                'appraisals as appraisal_pending' => fn($q) => $q->where('period_id', $activePeriod->id)->where('is_finalized', false)
            ])
            ->orderBy('name')
            ->get();

        return $divisions->map(function ($division) use ($kpiStats) {
            $avg = null;
            if (isset($kpiStats[$division->id]) && $kpiStats[$division->id]->total_weight > 0) {
                $avg = round($kpiStats[$division->id]->total_weighted / $kpiStats[$division->id]->total_weight, 2);
            }

            return [
                'division_id' => $division->id,
                'division_name' => $division->name,
                'leader_name' => $division->leader?->name ?? 'Belum ada leader',
                'staff_count' => $division->staff_count,
                'monthly_average' => $avg,
                'appraisal_finalized' => $division->appraisal_finalized,
                'appraisal_pending' => $division->appraisal_pending,
            ];
        })->toArray();
    }

    /**
     * Get active period
     * Cached for 60 seconds as it's used in every method
     */
    public function getActivePeriod(): ?Period
    {
        return cache()->remember('active_period', 60, function () {
            return Period::where('is_active', true)->first();
        });
    }
}
