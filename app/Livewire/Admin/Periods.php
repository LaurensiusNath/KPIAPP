<?php

namespace App\Livewire\Admin;

use App\Models\Period;
use App\Services\Exceptions\DomainValidationException;
use App\Services\PeriodService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class Periods extends Component
{
    use WithPagination;

    public $queryString = [
        'year' => ['except' => ''],
        'semester' => ['except' => ''],
    ];

    public string $year = '';
    public string $semester = ''; // '1' | '2'
    public bool $showCreateModal = false;

    public function openCreateModal(): void
    {
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
    }

    public function create(PeriodService $service): void
    {
        $this->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'semester' => 'required|in:1,2',
        ]);

        try {
            $service->createPeriod((int)$this->year, (int)$this->semester);
            session()->flash('success', 'Periode berhasil dibuat.');
            $this->resetPage();
            $this->showCreateModal = false;
            $this->reset(['year', 'semester']);
        } catch (DomainValidationException $e) {
            $this->addError('create', $e->getMessage());
        }
    }

    public function setActive(int $periodId, PeriodService $service): void
    {
        $period = Period::findOrFail($periodId);
        $service->setActivePeriod($period);
        session()->flash('success', 'Periode aktif diperbarui.');
        $this->resetPage();
    }

    public function getPeriodsProperty()
    {
        return Period::query()
            ->orderByDesc('is_active')
            ->orderBy('year', 'desc')
            ->orderBy('semester', 'desc')
            ->paginate(25)
            ->withQueryString();
    }

    public function render()
    {
        return view('livewire.admin.periods');
    }
}
