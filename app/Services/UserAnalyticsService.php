<?php

namespace App\Services;

use App\Models\Kpi;
use App\Models\KpiValue;
use App\Models\Period;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserAnalyticsService
{
    public function getMonthsForPeriod(Period $period): array
    {
        return $period->semester === 1 ? range(1, 6) : range(7, 12);
    }

    public function getUserMonthlyAverage(User $user, Period $period, int $month): ?float
    {
        // Get all KPI values with weights for weighted average calculation
        $kpiValues = KpiValue::query()
            ->join('kpis', 'kpi_values.kpi_id', '=', 'kpis.id')
            ->where('kpi_values.user_id', $user->id)
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

    public function getUserMonthlyKpiBreakdown(User $user, Period $period, int $month): array
    {
        $values = KpiValue::query()
            ->where('user_id', $user->id)
            ->where('period_id', $period->id)
            ->where('month', $month)
            ->where('is_submitted', true)
            ->get()
            ->keyBy('kpi_id');

        $result = Kpi::query()
            ->where('user_id', $user->id)
            ->where('period_id', $period->id)
            ->orderBy('title')
            ->get()
            ->map(function (Kpi $kpi) use ($values) {
                $value = $values->get($kpi->id);
                $score = $value ? round((float) $value->score, 2) : null;
                $criteriaLabel = null;

                if ($score !== null && $kpi->criteria_scale) {
                    $scoreInt = (int) $score;
                    // criteria_scale adalah array dengan key numerik (1-5) dan value description
                    if (isset($kpi->criteria_scale[$scoreInt])) {
                        $criteriaLabel = $kpi->criteria_scale[$scoreInt];
                    }
                }

                return [
                    'id' => $kpi->id,
                    'title' => $kpi->title,
                    'weight' => $kpi->weight,
                    'score' => $score,
                    'criteria_label' => $criteriaLabel,
                    'criteria_scale' => $kpi->criteria_scale, // debugging
                    'note' => $value?->note,
                ];
            })
            ->values()
            ->toArray();

        // Debugging: uncomment baris di bawah untuk melihat data
        // dd($result);

        return $result;
    }

    public function getTrendSeries(User $user, Period $period): array
    {
        $months = $this->getMonthsForPeriod($period);

        return collect($months)->map(function (int $month) use ($user, $period) {
            $avg = $this->getUserMonthlyAverage($user, $period, $month);
            return [
                'month' => $month,
                'label' => Carbon::create($period->year, $month, 1)->translatedFormat('F'),
                'average' => $avg,
            ];
        })->toArray();
    }
}
