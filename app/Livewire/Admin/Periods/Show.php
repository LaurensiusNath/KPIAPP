<?php

namespace App\Livewire\Admin\Periods;

use App\Models\Period;
use App\Services\PeriodService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public Period $period;

    public function mount(Period $period, PeriodService $periodService): void
    {
        $this->period = $periodService->loadKpiCount($period);
    }

    public function render()
    {
        return view('livewire.admin.periods.show');
    }
}
