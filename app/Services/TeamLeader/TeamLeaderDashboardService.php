<?php

namespace App\Services\TeamLeader;

use App\Models\Appraisal;
use App\Models\Division;
use App\Models\KpiValue;
use App\Models\Period;
use App\Models\User;
use Carbon\Carbon;

class TeamLeaderDashboardService
{
    /**
     * Get monthly performance trend for team members
     */
    public function getMonthlyTeamPerformance(Division $division, Period $period): array
    {
        $months = $period->semester === 1 ? range(1, 6) : range(7, 12);

        $staff = User::where('division_id', $division->id)
            ->where('role', 'user')
            ->orderBy('name')
            ->get();

        $result = [];
        foreach ($staff as $user) {
            $monthlyData = [];

            foreach ($months as $month) {
                $kpiValues = KpiValue::query()
                    ->join('kpis', 'kpi_values.kpi_id', '=', 'kpis.id')
                    ->where('kpi_values.user_id', $user->id)
                    ->where('kpi_values.period_id', $period->id)
                    ->where('kpi_values.month', $month)
                    ->where('kpi_values.is_submitted', true)
                    ->select('kpi_values.score', 'kpis.weight')
                    ->get();

                $average = null;
                if ($kpiValues->isNotEmpty()) {
                    $totalWeightedScore = 0;
                    $totalWeight = 0;
                    foreach ($kpiValues as $value) {
                        $totalWeightedScore += $value->score * $value->weight;
                        $totalWeight += $value->weight;
                    }
                    $average = $totalWeight > 0 ? round($totalWeightedScore / $totalWeight, 2) : null;
                }

                $monthlyData[] = [
                    'month' => $month,
                    'label' => Carbon::create($period->year, $month, 1)->translatedFormat('M'),
                    'average' => $average,
                ];
            }

            $result[] = [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'data' => $monthlyData,
            ];
        }

        return $result;
    }

    /**
     * Get evaluation status for specific month
     */
    public function getMonthlyEvaluationStatus(Division $division, Period $period, int $month): array
    {
        $totalStaff = User::where('division_id', $division->id)
            ->where('role', 'user')
            ->count();

        $evaluatedStaff = User::where('division_id', $division->id)
            ->where('role', 'user')
            ->whereHas('kpiValues', function ($q) use ($period, $month) {
                $q->where('period_id', $period->id)
                    ->where('month', $month)
                    ->where('is_submitted', true);
            })
            ->distinct()
            ->count();

        $pendingStaff = $totalStaff - $evaluatedStaff;
        $percentage = $totalStaff > 0 ? round(($evaluatedStaff / $totalStaff) * 100, 1) : 0;

        return [
            'total_staff' => $totalStaff,
            'evaluated_staff' => $evaluatedStaff,
            'pending_staff' => $pendingStaff,
            'percentage' => $percentage,
            'current_month' => Carbon::create($period->year, $month, 1)->translatedFormat('F'),
        ];
    }

    /**
     * Get appraisal status for team
     */
    public function getAppraisalStatus(Division $division, Period $period): array
    {
        $totalAppraisals = Appraisal::where('division_id', $division->id)
            ->where('period_id', $period->id)
            ->count();

        $pendingTL = Appraisal::where('division_id', $division->id)
            ->where('period_id', $period->id)
            ->whereNull('teamleader_submitted_at')
            ->count();

        $pendingHRD = Appraisal::where('division_id', $division->id)
            ->where('period_id', $period->id)
            ->whereNotNull('teamleader_submitted_at')
            ->whereNull('hrd_submitted_at')
            ->count();

        $finalized = Appraisal::where('division_id', $division->id)
            ->where('period_id', $period->id)
            ->where('is_finalized', true)
            ->count();

        return [
            'total' => $totalAppraisals,
            'pending_teamleader' => $pendingTL,
            'pending_hrd' => $pendingHRD,
            'finalized' => $finalized,
        ];
    }

    /**
     * Get top performers in team for specific month
     */
    public function getTopPerformers(Division $division, Period $period, int $month, int $limit = 3): array
    {
        $staff = User::where('division_id', $division->id)
            ->where('role', 'user')
            ->get();

        $performers = [];

        foreach ($staff as $user) {
            $kpiValues = KpiValue::query()
                ->join('kpis', 'kpi_values.kpi_id', '=', 'kpis.id')
                ->where('kpi_values.user_id', $user->id)
                ->where('kpi_values.period_id', $period->id)
                ->where('kpi_values.month', $month)
                ->where('kpi_values.is_submitted', true)
                ->select('kpi_values.score', 'kpis.weight')
                ->get();

            if ($kpiValues->isEmpty()) {
                continue;
            }

            $totalWeightedScore = 0;
            $totalWeight = 0;
            foreach ($kpiValues as $value) {
                $totalWeightedScore += $value->score * $value->weight;
                $totalWeight += $value->weight;
            }

            $average = $totalWeight > 0 ? round($totalWeightedScore / $totalWeight, 2) : 0;

            if ($average > 0) {
                $performers[] = [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'score' => $average,
                ];
            }
        }

        usort($performers, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($performers, 0, $limit);
    }

    /**
     * Get staff summary with statistics for specific month
     */
    public function getStaffSummary(Division $division, Period $period, int $month): array
    {
        $staff = User::where('division_id', $division->id)
            ->where('role', 'user')
            ->orderBy('name')
            ->get();

        return $staff->map(function (User $user) use ($period, $month) {
            // Calculate weighted average for specified month
            $kpiValues = KpiValue::query()
                ->join('kpis', 'kpi_values.kpi_id', '=', 'kpis.id')
                ->where('kpi_values.user_id', $user->id)
                ->where('kpi_values.period_id', $period->id)
                ->where('kpi_values.month', $month)
                ->where('kpi_values.is_submitted', true)
                ->select('kpi_values.score', 'kpis.weight')
                ->get();

            $monthlyAverage = null;
            if ($kpiValues->isNotEmpty()) {
                $totalWeightedScore = 0;
                $totalWeight = 0;
                foreach ($kpiValues as $value) {
                    $totalWeightedScore += $value->score * $value->weight;
                    $totalWeight += $value->weight;
                }
                $monthlyAverage = $totalWeight > 0 ? round($totalWeightedScore / $totalWeight, 2) : null;
            }

            // Check if evaluated this month
            $isEvaluated = $kpiValues->isNotEmpty();

            // Check appraisal status
            $appraisal = Appraisal::where('user_id', $user->id)
                ->where('period_id', $period->id)
                ->first();

            $appraisalStatus = 'Belum ada';
            if ($appraisal) {
                if ($appraisal->is_finalized) {
                    $appraisalStatus = 'Finalized';
                } elseif ($appraisal->teamleader_submitted_at) {
                    $appraisalStatus = 'Pending HRD';
                } else {
                    $appraisalStatus = 'Pending TL';
                }
            }

            return [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'monthly_average' => $monthlyAverage,
                'is_evaluated' => $isEvaluated,
                'appraisal_status' => $appraisalStatus,
            ];
        })->toArray();
    }

    /**
     * Get division statistics for specific month
     */
    public function getDivisionStats(Division $division, Period $period, int $month): array
    {
        // Total KPIs defined
        $totalKpis = \App\Models\Kpi::whereHas('user', function ($q) use ($division) {
            $q->where('division_id', $division->id);
        })
            ->where('period_id', $period->id)
            ->count();

        // Total evaluations submitted for specified month
        $totalEvaluations = KpiValue::where('division_id', $division->id)
            ->where('period_id', $period->id)
            ->where('month', $month)
            ->where('is_submitted', true)
            ->count();

        // Staff count
        $staffCount = User::where('division_id', $division->id)
            ->where('role', 'user')
            ->count();

        // Average score
        $avgScore = null;
        $scores = KpiValue::query()
            ->join('kpis', 'kpi_values.kpi_id', '=', 'kpis.id')
            ->where('kpi_values.division_id', $division->id)
            ->where('kpi_values.period_id', $period->id)
            ->where('kpi_values.month', $month)
            ->where('kpi_values.is_submitted', true)
            ->select('kpi_values.score', 'kpis.weight')
            ->get();

        if ($scores->isNotEmpty()) {
            $totalWeighted = 0;
            $totalWeight = 0;
            foreach ($scores as $v) {
                $totalWeighted += $v->score * $v->weight;
                $totalWeight += $v->weight;
            }
            $avgScore = $totalWeight > 0 ? round($totalWeighted / $totalWeight, 2) : null;
        }

        return [
            'total_kpis' => $totalKpis,
            'total_evaluations' => $totalEvaluations,
            'staff_count' => $staffCount,
            'monthly_average' => $avgScore,
        ];
    }

    public function getMonthsForPeriod(Period $period): array
    {
        return $period->semester === 1 ? range(1, 6) : range(7, 12);
    }
}
