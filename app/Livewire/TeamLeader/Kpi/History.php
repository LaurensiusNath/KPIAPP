<?php

namespace App\Livewire\TeamLeader\Kpi;

use App\Models\User;
use App\Services\KpiValueService;
use App\Services\PeriodService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.teamLeader')]
class History extends Component
{
    public User $user;
    public ?object $activePeriod = null;
    public array $byMonth = [];

    public function mount(User $user, KpiValueService $service, PeriodService $periodService)
    {
        $this->user = $user;
        $this->activePeriod = $periodService->getActivePeriod();
        if (!$this->activePeriod) {
            $this->byMonth = [];
            return;
        }
        $kpis = $service->getUserKpisForPeriod($this->user, $this->activePeriod);
        $kpiTitles = $kpis->keyBy('id')->map->title;

        for ($m = 1; $m <= 12; $m++) {
            $values = $service->getMonthlyValues($this->user, $this->activePeriod, $m);
            $items = [];
            $sum = 0;
            $count = 0;
            foreach ($kpis as $kpi) {
                $v = $values->get($kpi->id);
                $score = $v ? (int)($v->score ?? 0) : null;
                if ($score !== null) {
                    $sum += $score;
                    $count++;
                }
                $items[] = [
                    'title' => $kpi->title,
                    'score' => $score,
                    'submitted' => (bool)($v->is_submitted ?? false),
                ];
            }
            $avg = $count > 0 ? round($sum / $count, 2) : null;
            $this->byMonth[$m] = [
                'average' => $avg,
                'items' => $items,
            ];
        }
    }

    public function render()
    {
        return view('livewire.team-leader.kpi.history');
    }
}
