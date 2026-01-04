<?php

namespace App\Livewire\TeamLeader;

use App\Services\KpiService;
use App\Services\KpiValueService;
use App\Services\PeriodService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.teamLeader')]
class Members extends Component
{
    public array $members = [];
    public string $search = '';

    public function mount(KpiService $kpiService, KpiValueService $kpiValueService, PeriodService $periodService)
    {
        $tl = Auth::user();
        $period = $periodService->getActivePeriod();

        $collection = $kpiValueService->getMembersForTeamLeader($tl);

        $this->members = $collection->map(function ($u) use ($kpiService, $period) {
            $hasSetKpi = false;
            if ($period) {
                $totalWeight = $kpiService->getTotalWeightForUserPeriod($u, $period);
                $hasSetKpi = abs($totalWeight - 100.0) < 0.00001;
            }

            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'has_set_kpi' => $hasSetKpi,
            ];
        })->all();
    }

    public function getFilteredMembersProperty()
    {
        if (empty($this->search)) {
            return $this->members;
        }

        $searchLower = strtolower($this->search);
        return array_filter($this->members, function ($member) use ($searchLower) {
            return str_contains(strtolower($member['name']), $searchLower) ||
                str_contains(strtolower($member['email']), $searchLower);
        });
    }

    public function render()
    {
        return view('livewire.team-leader.members');
    }
}
