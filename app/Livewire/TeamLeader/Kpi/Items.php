<?php

namespace App\Livewire\TeamLeader\Kpi;

use App\Models\Period;
use App\Models\User;
use App\Services\KpiService;
use App\Services\PeriodService;
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

    public function mount(User $user, PeriodService $periodService, KpiService $kpiService): void
    {
        $actor = Auth::user();
        // Basic ownership check in UI (backend validates too)
        if ($actor->division_id === null || $user->division_id !== $actor->division_id) {
            // In production you may redirect or 403
            abort(403, 'Unauthorized');
        }

        $this->user = $user;

        $this->activePeriod = $periodService->getActivePeriod();
        if ($this->activePeriod) {
            $this->canEdit = $periodService->isCurrentWindowForKpiCreation($this->activePeriod);
            $this->totalWeight = $kpiService->getTotalWeightForUserPeriod($this->user, $this->activePeriod);
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
