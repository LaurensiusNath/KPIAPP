<?php

namespace App\Livewire\Admin\Periods;

use App\Services\Exceptions\DomainValidationException;
use App\Services\PeriodService;
use Livewire\Component;

class Create extends Component
{
    public string $year = '';
    public string $semester = '';

    public function create(PeriodService $service): void
    {
        $this->resetErrorBag();

        $this->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'semester' => 'required|in:1,2',
        ]);

        try {
            $service->createPeriod((int) $this->year, (int) $this->semester);
            session()->flash('success', 'Periode berhasil dibuat.');
            $this->dispatch('period-created');
            $this->reset(['year', 'semester']);
        } catch (DomainValidationException $e) {
            $this->addError('create', $e->getMessage());
        }
    }

    public function cancel(): void
    {
        $this->dispatch('period-create-canceled');
    }

    public function render()
    {
        return view('livewire.admin.periods.create');
    }
}
