<?php

namespace App\Livewire\TeamLeader\Kpi;

use App\Models\User;
use App\Services\TeamLeader\TeamLeaderKpiMonthlyEvaluationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.teamLeader')]
class Monthly extends Component
{
    public User $user;
    public ?int $activePeriodId = null;
    public int $month;
    public bool $readonly = false;

    public $kpis; // collection
    public array $scores = [];
    public array $notes = [];
    public array $scaleLegend = [];

    public function mount(User $user, TeamLeaderKpiMonthlyEvaluationService $service)
    {
        $this->user = $user;
        $actor = Auth::user();
        if (!$actor) {
            abort(403);
        }

        $loaded = $service->load($this->user, $actor);
        $this->activePeriodId = $loaded['activePeriodId'];
        $this->month = $loaded['month'];
        $this->kpis = $loaded['kpis'];
        $this->scaleLegend = $loaded['scaleLegend'];
        $this->scores = $loaded['scores'];
        $this->notes = $loaded['notes'];
        $this->readonly = $loaded['readonly'];

        if (!empty($loaded['errorMessage'])) {
            session()->flash('error', $loaded['errorMessage']);
        }
    }

    public function submit(TeamLeaderKpiMonthlyEvaluationService $service)
    {
        if ($this->readonly) {
            session()->flash('error', 'Form tidak dapat disubmit saat ini.');
            return;
        }

        $rules = [];
        foreach (array_keys($this->scores) as $kpiId) {
            $rules["scores.$kpiId"] = ['required', 'integer', 'min:1', 'max:5'];
            $rules["notes.$kpiId"] = ['nullable', 'string', 'max:1000'];
        }
        $this->validate($rules);

        $actor = Auth::user();
        if (!$actor) {
            abort(403);
        }

        $result = $service->submit($this->user, $actor, $this->scores, $this->notes);
        session()->flash($result['success'] ? 'success' : 'error', $result['message']);

        if ($result['success']) {
            $this->readonly = true;
        }
    }

    public function render()
    {
        return view('livewire.team-leader.kpi.monthly');
    }
}
