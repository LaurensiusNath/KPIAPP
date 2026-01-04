<?php

namespace App\Livewire\Admin\Appraisal\Divisions;

use App\Models\Division;
use App\Models\Period;
use App\Services\AppraisalService;
use App\Services\Admin\AdminAppraisalService;
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

    public function mount(PeriodService $periodService, AppraisalService $appraisalService, AdminAppraisalService $adminAppraisalService): void
    {
        $this->periods = $adminAppraisalService->getPeriodsForDivisionPicker();

        $this->period = $adminAppraisalService->getActivePeriod($periodService);
        $this->periodId = $this->period?->id;

        if ($this->period) {
            $this->loadDivisions($appraisalService);
        }
    }

    public function updatedPeriodId(PeriodService $periodService, AppraisalService $appraisalService): void
    {
        $this->period = $this->periodId
            ? $periodService->findById((int) $this->periodId)
            : null;
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

        $this->divisions = app(AdminAppraisalService::class)
            ->getDivisionRowsForPeriod($this->period, $appraisalService);
    }

    public function render()
    {
        return view('livewire.admin.appraisals.divisions.index');
    }
}
