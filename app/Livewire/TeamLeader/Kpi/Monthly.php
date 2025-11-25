<?php

namespace App\Livewire\TeamLeader\Kpi;

use App\Models\User;
use App\Services\Exceptions\DomainValidationException;
use App\Services\KpiValueService;
use App\Services\PeriodService;
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

    public function mount(User $user, KpiValueService $service, PeriodService $periodService)
    {
        $this->user = $user;
        $period = $periodService->getActivePeriod();
        $this->activePeriodId = $period?->id;
        $this->month = (int) now()->month;

        if (!$period) {
            $this->kpis = collect();
            $this->readonly = true;
            session()->flash('error', 'Periode aktif tidak ditemukan.');
            return;
        }

        // Validasi periode vs bulan berjalan dilakukan saat mount agar UI ramah
        try {
            $service->ensurePeriodMatchesCurrentDate($period);
        } catch (DomainValidationException $e) {
            $this->kpis = collect();
            $this->readonly = true;
            session()->flash('error', $e->getMessage());
            return;
        }

        $this->kpis = $service->getUserKpisForPeriod($this->user, $period);
        $this->scaleLegend = $this->buildScaleLegend($this->kpis);
        $values = $service->getMonthlyValues($this->user, $period, $this->month);

        foreach ($this->kpis as $kpi) {
            $value = $values->get($kpi->id);
            $this->scores[$kpi->id] = $value ? (int)($value->score ?? 0) : 0;
            $this->notes[$kpi->id] = $value?->note ?? '';
        }

        $this->readonly = $service->alreadySubmitted($this->user, $period, $this->month) || !$service->isEvaluationWindow();
    }

    public function submit(KpiValueService $service)
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

        $result = $service->submitMonthlyEvaluation($this->user, Auth::user(), $this->scores, $this->notes);
        session()->flash($result['success'] ? 'success' : 'error', $result['message']);

        if ($result['success']) {
            $this->readonly = true;
        }
    }

    protected function buildScaleLegend($kpis): array
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

    public function render()
    {
        return view('livewire.team-leader.kpi.monthly');
    }
}
