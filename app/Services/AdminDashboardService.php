<?php

namespace App\Services;

use App\Models\Appraisal;
use App\Models\Division;
use App\Models\KpiValue;
use App\Models\Period;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardService
{
    /**
     * Get monthly division performance for chart
     * Returns average KPI score per division per month
     */
    public function getMonthlyDivisionPerformance(): array
    {
        $activePeriod = $this->getActivePeriod();
        if (!$activePeriod) {
            return [];
        }

        $months = $activePeriod->semester === 1 ? range(1, 6) : range(7, 12);

        $divisions = Division::orderBy('name')->get();

        $result = [];
        foreach ($divisions as $division) {
            $monthlyData = [];

            foreach ($months as $month) {
                // Calculate weighted average for division for this month
                $kpiValues = KpiValue::query()
                    ->join('kpis', 'kpi_values.kpi_id', '=', 'kpis.id')
                    ->where('kpi_values.division_id', $division->id)
                    ->where('kpi_values.period_id', $activePeriod->id)
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
     * Shows how many staff have been evaluated this month
     */
    public function getMonthlyEvaluationStatus(): array
    {
        $activePeriod = $this->getActivePeriod();
        if (!$activePeriod) {
            return [];
        }

        $currentMonth = (int) Carbon::now()->month;

        $divisions = Division::with('leader')->orderBy('name')->get();

        return $divisions->map(function (Division $division) use ($activePeriod, $currentMonth) {
            // Total staff in division
            $totalStaff = User::where('division_id', $division->id)
                ->where('role', 'user')
                ->count();

            // Staff evaluated this month
            $evaluatedStaff = User::where('division_id', $division->id)
                ->where('role', 'user')
                ->whereHas('kpiValues', function ($q) use ($activePeriod, $currentMonth) {
                    $q->where('period_id', $activePeriod->id)
                        ->where('month', $currentMonth)
                        ->where('is_submitted', true);
                })
                ->distinct()
                ->count();

            $percentage = $totalStaff > 0 ? round(($evaluatedStaff / $totalStaff) * 100, 1) : 0;

            return [
                'division_id' => $division->id,
                'division_name' => $division->name,
                'leader_name' => $division->leader?->name ?? 'Belum ada leader',
                'total_staff' => $totalStaff,
                'evaluated_staff' => $evaluatedStaff,
                'pending_staff' => $totalStaff - $evaluatedStaff,
                'percentage' => $percentage,
            ];
        })->toArray();
    }

    /**
     * Get appraisal status summary
     * Returns count of pending TL, pending HRD, and finalized appraisals
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

        // Pending Team Leader (not submitted by TL yet)
        $pendingTL = Appraisal::where('period_id', $activePeriod->id)
            ->whereNull('teamleader_submitted_at')
            ->count();

        // Pending HRD (TL submitted but HRD not yet)
        $pendingHRD = Appraisal::where('period_id', $activePeriod->id)
            ->whereNotNull('teamleader_submitted_at')
            ->whereNull('hrd_submitted_at')
            ->count();

        // Finalized (both submitted and marked as final)
        $finalized = Appraisal::where('period_id', $activePeriod->id)
            ->where('is_finalized', true)
            ->count();

        $total = Appraisal::where('period_id', $activePeriod->id)->count();

        return [
            'pending_teamleader' => $pendingTL,
            'pending_hrd' => $pendingHRD,
            'finalized' => $finalized,
            'total' => $total,
        ];
    }

    /**
     * Get top 3 performers for current month
     * Based on weighted average KPI score
     */
    public function getTopPerformers(int $limit = 3): array
    {
        $activePeriod = $this->getActivePeriod();
        if (!$activePeriod) {
            return [];
        }

        $currentMonth = (int) Carbon::now()->month;

        $users = User::where('role', 'user')
            ->with('division')
            ->get();

        $performers = [];

        foreach ($users as $user) {
            // Calculate weighted average for this user for current month
            $kpiValues = KpiValue::query()
                ->join('kpis', 'kpi_values.kpi_id', '=', 'kpis.id')
                ->where('kpi_values.user_id', $user->id)
                ->where('kpi_values.period_id', $activePeriod->id)
                ->where('kpi_values.month', $currentMonth)
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
                    'division' => $user->division?->name ?? '-',
                    'score' => $average,
                ];
            }
        }

        // Sort by score descending and take top N
        usort($performers, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($performers, 0, $limit);
    }

    /**
     * Get division summary with statistics
     */
    public function getDivisionSummary(): array
    {
        $activePeriod = $this->getActivePeriod();
        if (!$activePeriod) {
            return [];
        }

        $currentMonth = (int) Carbon::now()->month;

        $divisions = Division::with('leader')->orderBy('name')->get();

        return $divisions->map(function (Division $division) use ($activePeriod, $currentMonth) {
            // Count staff
            $staffCount = User::where('division_id', $division->id)
                ->where('role', 'user')
                ->count();

            // Calculate weighted average for current month
            $kpiValues = KpiValue::query()
                ->join('kpis', 'kpi_values.kpi_id', '=', 'kpis.id')
                ->where('kpi_values.division_id', $division->id)
                ->where('kpi_values.period_id', $activePeriod->id)
                ->where('kpi_values.month', $currentMonth)
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

            // Count appraisal status
            $appraisalFinalized = Appraisal::where('division_id', $division->id)
                ->where('period_id', $activePeriod->id)
                ->where('is_finalized', true)
                ->count();

            $appraisalPending = Appraisal::where('division_id', $division->id)
                ->where('period_id', $activePeriod->id)
                ->where('is_finalized', false)
                ->count();

            return [
                'division_id' => $division->id,
                'division_name' => $division->name,
                'leader_name' => $division->leader?->name ?? 'Belum ada leader',
                'staff_count' => $staffCount,
                'monthly_average' => $monthlyAverage,
                'appraisal_finalized' => $appraisalFinalized,
                'appraisal_pending' => $appraisalPending,
            ];
        })->toArray();
    }

    /**
     * Get active period
     */
    public function getActivePeriod(): ?Period
    {
        return Period::where('is_active', true)->first();
    }
}
