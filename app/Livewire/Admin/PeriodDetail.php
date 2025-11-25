<?php

namespace App\Livewire\Admin;

use App\Models\Period;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class PeriodDetail extends Component
{
    public Period $period;

    public function mount(Period $period): void
    {
        $this->period = $period->loadCount('kpis');
    }

    public function render()
    {
        return view('livewire.admin.period-detail');
    }
}
