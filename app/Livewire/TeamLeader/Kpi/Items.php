<?php

namespace App\Livewire\TeamLeader\Kpi;

use App\Models\Period;
use App\Models\User;
use App\Services\KpiService;
use App\Services\PeriodService;
use App\Services\TeamLeader\TeamLeaderKpiItemService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.teamLeader')]
class Items extends Component
{
    public User $user;
    public ?Period $activePeriod = null;
    public bool $canEdit = false;
    public float $totalWeight = 0.0;

    public function mount(User $user, PeriodService $periodService, KpiService $kpiService, TeamLeaderKpiItemService $tlKpiItemService): void
    {
        $actor = Auth::user();
        if (!$actor) abort(403);
        try {
            $tlKpiItemService->ensureActorCanManageUser($actor, $user);
        } catch (\App\Services\Exceptions\UnauthorizedException $e) {
            abort(403, $e->getMessage());
        }

        $this->user = $user;

        $this->activePeriod = $tlKpiItemService->getActivePeriod($periodService);
        if ($this->activePeriod) {
            $this->canEdit = $tlKpiItemService->isCreationWindowOpen($periodService, $this->activePeriod);
            $this->totalWeight = $tlKpiItemService->getTotalWeightForUserPeriod($kpiService, $this->user, $this->activePeriod);
        }
    }

    public function getKpisProperty(KpiService $service)
    {
        return $this->activePeriod
            ? $service->getKpisByUserAndPeriod($this->user, $this->activePeriod)
            : collect();
    }

    // All create/edit/delete actions are now handled in PlanForm

    public function render()
    {
        return view('livewire.team-leader.kpi.items');
    }
}
