<?php

namespace App\Livewire\Admin;

use App\Services\AdminDashboardService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Dashboard extends Component
{
    public array $monthlyPerformance = [];
    public array $evaluationStatus = [];
    public array $appraisalStatus = [];
    public array $topPerformers = [];
    public array $divisionSummary = [];
    public $activePeriod = null;

    public function mount(AdminDashboardService $service): void
    {
        $this->monthlyPerformance = $service->getMonthlyDivisionPerformance();
        $this->evaluationStatus = $service->getMonthlyEvaluationStatus();
        $this->appraisalStatus = $service->getAppraisalStatus();
        $this->topPerformers = $service->getTopPerformers();
        $this->divisionSummary = $service->getDivisionSummary();
        $this->activePeriod = $service->getActivePeriod();
    }

    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}
