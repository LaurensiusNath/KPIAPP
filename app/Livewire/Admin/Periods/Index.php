<?php

namespace App\Livewire\Admin\Periods;

use App\Services\Admin\AdminPeriodService;
use App\Services\PeriodService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class Index extends Component
{
    use WithPagination;

    public bool $showCreateModal = false;

    public function mount(): void
    {
        if (request()->routeIs('admin.periods.create')) {
            $this->showCreateModal = true;
        }
    }

    public function openCreateModal(): void
    {
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
    }

    public function setActive(int $periodId, PeriodService $service): void
    {
        $service->setActivePeriodById($periodId);
        session()->flash('success', 'Periode aktif diperbarui.');
        $this->resetPage();
    }

    public function getPeriodsProperty(AdminPeriodService $adminPeriodService): LengthAwarePaginator
    {
        return $adminPeriodService
            ->paginateForIndex()
            ->withQueryString();
    }

    #[On('period-created')]
    public function handlePeriodCreated(): void
    {
        $this->showCreateModal = false;
        $this->resetPage();
    }

    #[On('period-create-canceled')]
    public function handleCreateCanceled(): void
    {
        $this->showCreateModal = false;
    }

    public function render()
    {
        return view('livewire.admin.periods.index');
    }
}
