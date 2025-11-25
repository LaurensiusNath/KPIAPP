<?php

namespace App\Livewire\Admin\Appraisal\Divisions;

use App\Models\Division;
use App\Models\Period;
use App\Services\AppraisalService;
use App\Services\PeriodService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Index extends Component
{
    public ?Period $period = null;
    public $periods = [];
    public ?int $periodId = null;
    public array $divisions = [];

    public function mount(PeriodService $periodService, AppraisalService $appraisalService): void
    {
        $this->periods = Period::query()
            ->orderByDesc('is_active')
            ->orderBy('year', 'desc')
            ->orderBy('semester', 'desc')
            ->get();

        $this->period = $periodService->getActivePeriod();
        $this->periodId = $this->period?->id;

        if ($this->period) {
            $this->loadDivisions($appraisalService);
        }
    }

    public function updatedPeriodId(AppraisalService $appraisalService): void
    {
        $this->period = Period::find($this->periodId);
        if ($this->period) {
            $this->loadDivisions($appraisalService);
        }
    }

    protected function loadDivisions(AppraisalService $appraisalService): void
    {
        if (!$this->period) {
            $this->divisions = [];
            return;
        }

        $allDivisions = Division::orderBy('name')->get();

        $this->divisions = $allDivisions->map(function (Division $division) use ($appraisalService) {
            $summary = $appraisalService->getDivisionAppraisalSummary($division, $this->period);

            return [
                'id' => $division->id,
                'name' => $division->name,
                'staff_count' => $summary['staff_count'],
                'overall_average' => $summary['overall_average'],
            ];
        })->toArray();
    }

    public function render()
    {
        return view('livewire.admin.appraisal.divisions.index');
    }
}
