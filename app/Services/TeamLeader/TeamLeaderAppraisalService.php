<?php

namespace App\Services\TeamLeader;

use App\Models\Appraisal;
use App\Models\Period;
use App\Models\User;
use App\Services\PeriodService;

class TeamLeaderAppraisalService
{
    public function getActivePeriod(PeriodService $periodService): ?Period
    {
        return $periodService->getActivePeriod();
    }

    public function isSubmissionWindowOpen(PeriodService $periodService, Period $period): bool
    {
        return $periodService->isCurrentWindowForAppraisal($period);
    }

    public function getSubmissionWindowMessage(Period $period): string
    {
        return $period->semester === 1
            ? 'Penilaian appraisal semester 1 dibuka 25-31 Juni.'
            : 'Penilaian appraisal semester 2 dibuka 25-31 Desember.';
    }

    public function findAppraisal(User $user, Period $period): ?Appraisal
    {
        return Appraisal::query()
            ->where('user_id', $user->id)
            ->where('period_id', $period->id)
            ->first();
    }
}
