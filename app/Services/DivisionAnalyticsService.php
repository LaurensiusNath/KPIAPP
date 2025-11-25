<?php

namespace App\Services;

use App\Models\Division;
use App\Models\KpiValue;
use App\Models\Period;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DivisionAnalyticsService
{
    /**
     * @return int[]
     */
    public function getMonthsForPeriod(Period $period): array
    {
        return $period->semester === 1
            ? range(1, 6)
            : range(7, 12);
    }

    public function getDivisionMonthlyAverage(Division $division, Period $period, int $month): ?float
    {
        // Get all KPI values with weights for weighted average calculation
        $kpiValues = KpiValue::query()
            ->join('kpis', 'kpi_values.kpi_id', '=', 'kpis.id')
            ->where('kpi_values.division_id', $division->id)
            ->where('kpi_values.period_id', $period->id)
            ->where('kpi_values.month', $month)
            ->where('kpi_values.is_submitted', true)
            ->select('kpi_values.score', 'kpis.weight')
            ->get();

        if ($kpiValues->isEmpty()) {
            return null;
        }

        // Calculate weighted average: sum(score * weight) / sum(weight)
        $totalWeightedScore = 0;
        $totalWeight = 0;

        foreach ($kpiValues as $value) {
            $totalWeightedScore += $value->score * $value->weight;
            $totalWeight += $value->weight;
        }

        return $totalWeight > 0 ? round($totalWeightedScore / $totalWeight, 2) : null;
    }

    public function getDivisionUserMonthlyScores(Division $division, Period $period, int $month): Collection
    {
        $users = User::query()
            ->where('division_id', $division->id)
            ->orderBy('name')
            ->get();

        return $users->map(function (User $user) use ($period, $month) {
            // Calculate weighted average for each user
            $kpiValues = KpiValue::query()
                ->join('kpis', 'kpi_values.kpi_id', '=', 'kpis.id')
                ->where('kpi_values.user_id', $user->id)
                ->where('kpi_values.period_id', $period->id)
                ->where('kpi_values.month', $month)
                ->where('kpi_values.is_submitted', true)
                ->select('kpi_values.score', 'kpis.weight')
                ->get();

            $avgScore = null;
            if ($kpiValues->isNotEmpty()) {
                $totalWeightedScore = 0;
                $totalWeight = 0;
                foreach ($kpiValues as $value) {
                    $totalWeightedScore += $value->score * $value->weight;
                    $totalWeight += $value->weight;
                }
                $avgScore = $totalWeight > 0 ? round($totalWeightedScore / $totalWeight, 2) : null;
            }

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avg_score' => $avgScore,
            ];
        });
    }

    public function getDivisionTrendSeries(Division $division, Period $period): array
    {
        $months = $this->getMonthsForPeriod($period);

        return collect($months)->map(function (int $month) use ($division, $period) {
            $average = $this->getDivisionMonthlyAverage($division, $period, $month);
            return [
                'month' => $month,
                'label' => $this->formatMonthLabel($period, $month),
                'average' => $average,
            ];
        })->toArray();
    }

    protected function formatMonthLabel(Period $period, int $month): string
    {
        return Carbon::create($period->year, $month, 1)->translatedFormat('F');
    }
}
