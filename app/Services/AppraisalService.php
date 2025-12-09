<?php

namespace App\Services;

use App\Models\Appraisal;
use App\Models\Division;
use App\Models\Kpi;
use App\Models\KpiValue;
use App\Models\Period;
use App\Models\User;
use App\Services\Exceptions\DomainValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AppraisalService
{
    public function getSemesterMonths(Period $period): array
    {
        return $period->semester === 1 ? [1, 2, 3, 4, 5, 6] : [7, 8, 9, 10, 11, 12];
    }

    /**
     * Get division appraisal summary for 6 months
     */
    public function getDivisionAppraisalSummary(Division $division, Period $period): array
    {
        $months = $this->getSemesterMonths($period);

        // Get all staff in division
        $staffCount = User::where('division_id', $division->id)
            ->where('role', 'user')
            ->count();

        // Get weighted average KPI per month across all staff
        $monthlyAverages = [];
        foreach ($months as $month) {
            $kpiValues = KpiValue::query()
                ->join('kpis', 'kpi_values.kpi_id', '=', 'kpis.id')
                ->where('kpi_values.division_id', $division->id)
                ->where('kpi_values.period_id', $period->id)
                ->where('kpi_values.month', $month)
                ->where('kpi_values.is_submitted', true)
                ->select('kpi_values.score', 'kpis.weight')
                ->get();

            if ($kpiValues->isEmpty()) {
                $monthlyAverages[$month] = null;
            } else {
                $totalWeightedScore = 0;
                $totalWeight = 0;
                foreach ($kpiValues as $value) {
                    $totalWeightedScore += $value->score * $value->weight;
                    $totalWeight += $value->weight;
                }
                $monthlyAverages[$month] = $totalWeight > 0 ? round($totalWeightedScore / $totalWeight, 2) : null;
            }
        }

        // Overall 6-month average
        $validAverages = array_filter($monthlyAverages, fn($v) => $v !== null);
        $overallAverage = count($validAverages) > 0
            ? round(array_sum($validAverages) / count($validAverages), 2)
            : null;

        return [
            'division' => $division,
            'period' => $period,
            'months' => $months,
            'staff_count' => $staffCount,
            'monthly_averages' => $monthlyAverages,
            'overall_average' => $overallAverage,
        ];
    }

    /**
     * Get division trend series for chart
     */
    public function getDivisionTrendSeries(Division $division, Period $period): array
    {
        $months = $this->getSemesterMonths($period);

        return collect($months)->map(function (int $month) use ($division, $period) {
            $kpiValues = KpiValue::query()
                ->join('kpis', 'kpi_values.kpi_id', '=', 'kpis.id')
                ->where('kpi_values.division_id', $division->id)
                ->where('kpi_values.period_id', $period->id)
                ->where('kpi_values.month', $month)
                ->where('kpi_values.is_submitted', true)
                ->select('kpi_values.score', 'kpis.weight')
                ->get();

            $avg = null;
            if ($kpiValues->isNotEmpty()) {
                $totalWeightedScore = 0;
                $totalWeight = 0;
                foreach ($kpiValues as $value) {
                    $totalWeightedScore += $value->score * $value->weight;
                    $totalWeight += $value->weight;
                }
                $avg = $totalWeight > 0 ? round($totalWeightedScore / $totalWeight, 2) : null;
            }

            return [
                'month' => $month,
                'label' => Carbon::create($period->year, $month, 1)->translatedFormat('F'),
                'average' => $avg,
            ];
        })->toArray();
    }

    /**
     * Get staff appraisal list for a division
     */
    public function getStaffAppraisalList(Division $division, Period $period): array
    {
        $months = $this->getSemesterMonths($period);

        $staff = User::query()
            ->where('division_id', $division->id)
            ->where('role', 'user')
            ->orderBy('name')
            ->get();

        return $staff->map(function (User $user) use ($period, $months) {
            // Get 6-month weighted average
            $kpiValues = KpiValue::query()
                ->join('kpis', 'kpi_values.kpi_id', '=', 'kpis.id')
                ->where('kpi_values.user_id', $user->id)
                ->where('kpi_values.period_id', $period->id)
                ->whereIn('kpi_values.month', $months)
                ->where('kpi_values.is_submitted', true)
                ->select('kpi_values.score', 'kpis.weight')
                ->get();

            $avg = null;
            if ($kpiValues->isNotEmpty()) {
                $totalWeightedScore = 0;
                $totalWeight = 0;
                foreach ($kpiValues as $value) {
                    $totalWeightedScore += $value->score * $value->weight;
                    $totalWeight += $value->weight;
                }
                $avg = $totalWeight > 0 ? round($totalWeightedScore / $totalWeight, 2) : null;
            }

            // Get appraisal status
            $appraisal = Appraisal::query()
                ->where('user_id', $user->id)
                ->where('period_id', $period->id)
                ->first();

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'average_score' => $avg,
                'appraisal_status' => $appraisal?->is_finalized ? 'Finalized' : ($appraisal ? 'In Progress' : 'Not Started'),
                'teamleader_submitted' => $appraisal?->teamleader_submitted_at !== null,
                'hrd_submitted' => $appraisal?->hrd_submitted_at !== null,
            ];
        })->toArray();
    }

    /**
     * Get staff appraisal detail
     */
    public function getStaffAppraisalDetail(User $user, Period $period): array
    {
        $months = $this->getSemesterMonths($period);

        // Get all KPIs with values
        $kpis = Kpi::query()
            ->where('user_id', $user->id)
            ->where('period_id', $period->id)
            ->orderBy('title')
            ->get();

        $kpiDetails = $kpis->map(function (Kpi $kpi) use ($period, $months) {
            $monthlyScores = [];
            $validScores = [];

            foreach ($months as $month) {
                $value = KpiValue::query()
                    ->where('kpi_id', $kpi->id)
                    ->where('period_id', $period->id)
                    ->where('month', $month)
                    ->where('is_submitted', true)
                    ->first();

                $monthlyScores[$month] = [
                    'score' => $value?->score,
                    'note' => $value?->note,
                ];

                if ($value?->score !== null) {
                    $validScores[] = $value->score;
                }
            }

            // Simple average for single KPI across months (not weighted)
            $average = count($validScores) > 0
                ? round(array_sum($validScores) / count($validScores), 2)
                : null;

            return [
                'id' => $kpi->id,
                'title' => $kpi->title,
                'weight' => $kpi->weight,
                'criteria_scale' => $kpi->criteria_scale,
                'monthly_scores' => $monthlyScores,
                'average' => $average,
            ];
        })->toArray();

        // Compute overall averages across KPIs
        $totalWeight = 0.0;
        $weightedAccumulator = 0.0;
        $plainAccumulator = 0.0;
        $countWithAvg = 0;

        foreach ($kpiDetails as $kpi) {
            $avg = $kpi['average'];
            if ($avg !== null) {
                $countWithAvg++;
                $plainAccumulator += (float)$avg;
                $w = (float)($kpi['weight'] ?? 0);
                $totalWeight += $w;
                $weightedAccumulator += ($avg * $w);
            }
        }

        $overallAverage = $countWithAvg > 0 ? round($plainAccumulator / $countWithAvg, 2) : null;
        $overallWeightedAverage = null;
        if ($countWithAvg > 0) {
            if ($totalWeight > 0) {
                $overallWeightedAverage = round($weightedAccumulator / $totalWeight, 2);
            } else {
                $overallWeightedAverage = round($plainAccumulator / $countWithAvg, 2);
            }
        }

        // Get appraisal record
        $appraisal = Appraisal::query()
            ->where('user_id', $user->id)
            ->where('period_id', $period->id)
            ->first();

        return [
            'user' => $user,
            'period' => $period,
            'months' => $months,
            'kpis' => $kpiDetails,
            'overall_average' => $overallAverage,
            'overall_weighted_average' => $overallWeightedAverage,
            'appraisal' => $appraisal,
        ];
    }

    public function getSemesterSummary(int $userId, int $periodId): array
    {
        $user = User::with('division')->findOrFail($userId);
        $period = Period::findOrFail($periodId);
        $months = $this->getSemesterMonths($period);

        // KPI list for user+period
        $kpis = Kpi::query()
            ->where('user_id', $user->id)
            ->where('period_id', $period->id)
            ->with(['values' => function ($q) use ($months, $period) {
                $q->where('period_id', $period->id)->whereIn('month', $months);
            }])
            ->orderBy('title')
            ->get();

        $summaryKpis = [];
        $totalWeight = 0.0;
        $weightedAccumulator = 0.0;
        $plainAccumulator = 0.0;
        $kpiCount = max($kpis->count(), 1);

        foreach ($kpis as $kpi) {
            $weight = (float)$kpi->weight;
            $totalWeight += $weight;
            $monthlyScores = [];
            foreach ($months as $m) {
                $value = $kpi->values->first(function ($v) use ($m) {
                    return (int)$v->month === (int)$m;
                });
                $monthlyScores[$m] = $value?->score ?? null;
            }
            // Average per KPI (ignore null months)
            $validScores = array_filter($monthlyScores, fn($s) => $s !== null);
            $avg = count($validScores) ? array_sum($validScores) / count($validScores) : 0.0;
            $summaryKpis[] = [
                'id' => $kpi->id,
                'title' => $kpi->title,
                'weight' => $weight,
                'monthly_scores' => $monthlyScores,
                'average_score' => round($avg, 2),
                'weighted_average' => round($avg * $weight, 2),
            ];
            $plainAccumulator += $avg;
            $weightedAccumulator += ($avg * $weight);
        }

        $totalAverage = round($plainAccumulator / $kpiCount, 2);
        $totalWeightedAverage = $totalWeight > 0 ? round($weightedAccumulator / $totalWeight, 2) : round($plainAccumulator / $kpiCount, 2);

        return [
            'user' => $user,
            'period' => $period,
            'months' => $months,
            'kpis' => $summaryKpis,
            'total_average' => $totalAverage,
            'total_weighted_average' => $totalWeightedAverage,
        ];
    }

    public function saveTeamLeaderAppraisal(int $userId, int $periodId, array $data): array
    {
        $user = User::with('division')->findOrFail($userId);
        $period = Period::findOrFail($periodId);
        $tl = Auth::user();

        if (($tl->role ?? null) !== 'team-leader') {
            throw new DomainValidationException('Hanya Team Leader yang boleh submit appraisal ini.');
        }
        if ($tl->division_id !== $user->division_id) {
            throw new DomainValidationException('User bukan anggota divisi Anda.');
        }
        if (!$period->is_active) {
            throw new DomainValidationException('Periode tidak aktif.');
        }

        $comment = trim($data['comment_teamleader'] ?? '');
        if (strlen($comment) < 10) {
            throw new DomainValidationException('Komentar Team Leader minimal 10 karakter.');
        }

        $appraisal = Appraisal::firstOrCreate([
            'user_id' => $user->id,
            'period_id' => $period->id,
        ], [
            'division_id' => $user->division_id,
            'evaluator_id' => $tl->id,
        ]);

        if ($appraisal->teamleader_submitted_at) {
            return ['success' => false, 'message' => 'Team Leader sudah submit sebelumnya.'];
        }

        // Compute final_score if not yet computed
        if ($appraisal->final_score === null) {
            $summary = $this->getSemesterSummary($user->id, $period->id);
            $appraisal->final_score = $summary['total_weighted_average'];
        }

        $appraisal->comment_teamleader = $comment;
        $appraisal->teamleader_submitted_at = now();
        $appraisal->save();

        $this->finalizeIfCompleted($appraisal);

        return ['success' => true, 'message' => $appraisal->is_finalized ? 'Appraisal finalized.' : 'Appraisal TL berhasil disimpan.'];
    }

    public function saveHrdAppraisal(int $userId, int $periodId, array $data): array
    {
        $admin = Auth::user();
        if (($admin->role ?? null) !== 'admin') {
            throw new DomainValidationException('Hanya HRD/Admin yang boleh submit appraisal ini.');
        }
        $user = User::with('division')->findOrFail($userId);
        $period = Period::findOrFail($periodId);
        if (!$period->is_active) {
            throw new DomainValidationException('Periode tidak aktif.');
        }

        $comment = trim($data['comment_hrd'] ?? '');
        if (strlen($comment) < 10) {
            throw new DomainValidationException('Komentar HRD minimal 10 karakter.');
        }

        $appraisal = Appraisal::firstOrCreate([
            'user_id' => $user->id,
            'period_id' => $period->id,
        ], [
            'division_id' => $user->division_id,
            'evaluator_id' => $admin->id,
        ]);

        if (!$appraisal->teamleader_submitted_at) {
            return ['success' => false, 'message' => 'Menunggu Team Leader submit terlebih dahulu.'];
        }
        if ($appraisal->hrd_submitted_at) {
            return ['success' => false, 'message' => 'HRD sudah submit sebelumnya.'];
        }

        // Ensure final_score exists
        if ($appraisal->final_score === null) {
            $summary = $this->getSemesterSummary($user->id, $period->id);
            $appraisal->final_score = $summary['total_weighted_average'];
        }

        $appraisal->comment_hrd = $comment;
        $appraisal->hrd_submitted_at = now();
        $appraisal->save();

        $this->finalizeIfCompleted($appraisal);

        return ['success' => true, 'message' => $appraisal->is_finalized ? 'Appraisal finalized.' : 'Appraisal HRD berhasil disimpan.'];
    }

    public function finalizeIfCompleted(Appraisal $appraisal): void
    {
        if ($appraisal->teamleader_submitted_at && $appraisal->hrd_submitted_at && !$appraisal->is_finalized) {
            $appraisal->is_finalized = true;
            $appraisal->save();
        }
    }

    // ===================== INDEX HELPER METHODS =====================
    public function getPeriodsForIndex()
    {
        return Period::query()->orderByDesc('year')->orderByDesc('semester')->get();
    }

    public function getUsersForIndex()
    {
        return User::query()
            ->where('role', 'user')
            ->with('division')
            ->orderBy('name')
            ->get();
    }

    public function getUsersForIndexPaginated(int $perPage = 10)
    {
        return User::query()
            ->where('role', 'user')
            ->with('division')
            ->orderBy('name')
            ->paginate($perPage)->withQueryString();
    }

    public function getAppraisalsForPeriod(?int $periodId)
    {
        if (!$periodId) {
            return collect();
        }
        return Appraisal::where('period_id', $periodId)->get()->keyBy('user_id');
    }
}
