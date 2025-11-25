<?php

namespace App\Livewire\TeamLeader\Kpi;

use App\Models\User;
use App\Services\KpiValueService;
use App\Services\PeriodService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.teamLeader')]
class Members extends Component
{
    public array $members = [];
    public int $month;
    public string $mode = 'manage';

    public function mount(KpiValueService $service, PeriodService $periodService)
    {
        $tl = Auth::user();
        $period = $periodService->getActivePeriod();
        $this->month = (int) now()->month;
        $this->mode = request()->routeIs('tl.kpi.members') ? 'monthly' : 'manage';

        $collection = $service->getMembersForTeamLeader($tl);
        $this->members = $collection->map(function (User $u) use ($service, $period) {
            $status = 'Pending';
            if ($period) {
                $submitted = $service->alreadySubmitted($u, $period, (int)now()->month);
                $status = $submitted ? 'Submitted' : 'Pending';
            }
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'status' => $status,
            ];
        })->all();
    }

    public function render()
    {
        return view('livewire.team-leader.kpi.members');
    }
}
