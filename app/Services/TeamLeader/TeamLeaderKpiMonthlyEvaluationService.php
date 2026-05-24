<?php

namespace App\Services\TeamLeader;

use App\Models\User;
use App\Services\Exceptions\DomainValidationException;
use App\Services\KpiValueService;
use App\Services\PeriodService;

class TeamLeaderKpiMonthlyEvaluationService
{
    public function __construct(
        private readonly KpiValueService $kpiValueService,
        private readonly PeriodService $periodService,
    ) {}

    /**
     * Loads the Monthly KPI evaluation context for a team leader assessing a member.
     * Returns: activePeriodId, month, kpis, scaleLegend, scores, notes, readonly, errorMessage.
     */
    public function load(User $member, User $actor, ?int $month = null): array
    {
        $month = $month ?? (int) now()->month;
        $period = $this->periodService->getActivePeriod();

        // Authorization/scope
        $this->kpiValueService->ensureTeamLeader($actor);
        $this->kpiValueService->ensureSameDivision($actor, $member);

        if (!$period) {
            return [
                'activePeriodId' => null,
                'month' => $month,
                'kpis' => collect(),
                'scaleLegend' => [],
                'scores' => [],
                'notes' => [],
                'readonly' => true,
                'errorMessage' => 'Periode aktif tidak ditemukan.',
            ];
        }

        try {
            $this->kpiValueService->ensurePeriodMatchesCurrentDate($period);
        } catch (DomainValidationException $e) {
            return [
                'activePeriodId' => $period->id,
                'month' => $month,
                'kpis' => collect(),
                'scaleLegend' => [],
                'scores' => [],
                'notes' => [],
                'readonly' => true,
                'errorMessage' => $e->getMessage(),
            ];
        }

        $kpis = $this->kpiValueService->getUserKpisForPeriod($member, $period);
        $scaleLegend = $this->buildScaleLegend($kpis);
        $values = $this->kpiValueService->getMonthlyValues($member, $period, $month);

        $scores = [];
        $notes = [];
        foreach ($kpis as $kpi) {
            $value = $values->get($kpi->id);
            $scores[$kpi->id] = $value ? (int) ($value->score ?? 0) : 0;
            $notes[$kpi->id] = $value?->note ?? '';
        }

        $readonly = $this->kpiValueService->alreadySubmitted($member, $period, $month)
            || !$this->kpiValueService->isEvaluationWindow();

        return [
            'activePeriodId' => $period->id,
            'month' => $month,
            'kpis' => $kpis,
            'scaleLegend' => $scaleLegend,
            'scores' => $scores,
            'notes' => $notes,
            'readonly' => $readonly,
            'errorMessage' => null,
        ];
    }

    public function submit(User $member, User $actor, array $scores, array $notes): array
    {
        return $this->kpiValueService->submitMonthlyEvaluation($member, $actor, $scores, $notes);
    }

    private function buildScaleLegend($kpis): array
    {
        $legend = [];

        foreach ($kpis as $kpi) {
            $raw = is_array($kpi->criteria_scale) ? $kpi->criteria_scale : [];
            $normalized = [];
            for ($score = 1; $score <= 5; $score++) {
                $normalized[$score] = (string) ($raw[$score] ?? ($raw[(string) $score] ?? ''));
            }
            $legend[$kpi->id] = $normalized;
        }

        return $legend;
    }
}
