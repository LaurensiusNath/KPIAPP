<?php

declare(strict_types=1);

namespace App\Services\TeamLeader;

use App\Models\Period;
use App\Models\User;
use App\Services\Exceptions\UnauthorizedException;
use App\Services\KpiService;
use App\Services\PeriodService;
use Illuminate\Support\Collection;

class TeamLeaderKpiItemService
{
    public function ensureActorCanManageUser(User $actor, User $user): void
    {
        if ($actor->division_id === null || $user->division_id !== $actor->division_id) {
            throw new UnauthorizedException('User not in your division');
        }
    }

    public function getActivePeriod(PeriodService $periodService): ?Period
    {
        return $periodService->getActivePeriod();
    }

    public function isCreationWindowOpen(PeriodService $periodService, Period $period): bool
    {
        return $periodService->isCurrentWindowForKpiCreation($period);
    }

    /**
     * @return array<int, array{id:int|null, title:string, weight:string, scale:array<int,string>, deleted:bool}>
     */
    public function buildPlanItemsFromExistingKpis(Collection $existingKpis): array
    {
        return $existingKpis->map(function ($kpi) {
            $rawScale = is_array($kpi->criteria_scale) ? $kpi->criteria_scale : [];
            $normScale = [];
            for ($lvl = 1; $lvl <= 5; $lvl++) {
                $normScale[$lvl] = (string)($rawScale[$lvl] ?? ($rawScale[(string)$lvl] ?? ''));
            }

            return [
                'id' => $kpi->id,
                'title' => (string) $kpi->title,
                'weight' => (string) number_format((float) $kpi->weight, 2, '.', ''),
                'scale' => $normScale,
                'deleted' => false,
            ];
        })->values()->all();
    }

    /**
     * @return array{id:null, title:string, weight:string, scale:array<int,string>, deleted:bool}
     */
    public function newPlanRow(): array
    {
        return [
            'id' => null,
            'title' => '',
            'weight' => '',
            'scale' => [
                1 => 'Very Poor',
                2 => 'Poor',
                3 => 'Average',
                4 => 'Good',
                5 => 'Excellent',
            ],
            'deleted' => false,
        ];
    }

    /** @param array<int, array{weight:mixed, deleted:bool}> $items */
    public function calculateTotalWeight(array $items): float
    {
        $sum = 0.0;
        foreach ($items as $row) {
            if (!empty($row['deleted'])) {
                continue;
            }
            $sum += (float)($row['weight'] ?? 0);
        }
        return $sum;
    }

    public function getTotalWeightForUserPeriod(KpiService $kpiService, User $user, Period $period): float
    {
        return $kpiService->getTotalWeightForUserPeriod($user, $period);
    }
}
