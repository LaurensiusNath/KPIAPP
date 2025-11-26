<?php

namespace App\Livewire\TeamLeader\Kpi;

use App\Models\Appraisal;
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
    public string $search = '';

    public function mount(KpiValueService $service, PeriodService $periodService)
    {
        $tl = Auth::user();
        $period = $periodService->getActivePeriod();
        $this->month = (int) now()->month;

        $collection = $service->getMembersForTeamLeader($tl);
        $this->members = $collection->map(function (User $u) use ($service, $period) {
            $monthlyStatus = 'Belum Dinilai';
            $appraisalStatus = 'Belum Ada';

            if ($period) {
                // Status penilaian bulanan
                $submitted = $service->alreadySubmitted($u, $period, $this->month);
                $monthlyStatus = $submitted ? 'Sudah Dinilai' : 'Belum Dinilai';

                // Status appraisal
                $appraisal = Appraisal::query()
                    ->where('user_id', $u->id)
                    ->where('period_id', $period->id)
                    ->first();

                if ($appraisal) {
                    if ($appraisal->is_finalized) {
                        $appraisalStatus = 'Finalized';
                    } elseif ($appraisal->teamleader_submitted_at) {
                        $appraisalStatus = 'Pending HRD';
                    } else {
                        $appraisalStatus = 'Pending TL';
                    }
                }
            }

            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'monthly_status' => $monthlyStatus,
                'appraisal_status' => $appraisalStatus,
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
        return view('livewire.team-leader.kpi.members');
    }
}
