<?php

namespace App\Livewire\Admin\Appraisal;

use App\Services\Admin\AdminAppraisalService;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Index extends Component
{
    use WithPagination;

    protected $queryString = [
        'periodId' => ['as' => 'period', 'except' => ''],
    ];

    public $periodId;
    public $periods; // Collection<Period>
    public int $perPage = 25;

    public function mount(AdminAppraisalService $adminAppraisalService)
    {
        $this->periods = $adminAppraisalService->getPeriodsForIndex();
        // periodId auto-populated from ?period= via queryString mapping
        if (!$this->periodId) {
            $this->periodId = $this->periods->first()?->id;
        }
    }

    public function updatedPeriodId()
    {
        // Redirect to ensure clean state & pagination reset
        return redirect()->route('admin.appraisals.index', ['period' => $this->periodId]);
    }

    public function changePeriod($value)
    {
        $this->periodId = $value;
        return redirect()->route('admin.appraisals.index', ['period' => $this->periodId]);
    }

    public function refresh()
    {
        $this->resetPage();
    }

    public function goToAppraisal($userId)
    {
        return redirect()->route('admin.appraisals.form', ['user' => $userId, 'period' => $this->periodId]);
    }

    public function render(AdminAppraisalService $adminAppraisalService)
    {
        $users = $adminAppraisalService->getUsersForIndexPaginated($this->perPage);
        $appraisals = $adminAppraisalService->getAppraisalsForPeriod($this->periodId);

        return view('livewire.admin.appraisals.index', [
            'users' => $users,
            'appraisals' => $appraisals,
        ]);
    }
}
