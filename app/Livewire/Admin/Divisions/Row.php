<?php

namespace App\Livewire\Admin\Divisions;

use App\Models\Division;
use App\Services\DivisionService;
use Livewire\Component;

class Row extends Component
{
    public Division $division;
    public bool $menuOpen = false;
    public bool $confirmingDelete = false;
    public ?string $leaderName = null;
    public int $memberCount = 0;

    public function mount(Division $division): void
    {
        $this->division = $division->load('leader')->loadCount('users');
        $this->syncDerivedData();
    }

    public function hydrate(): void
    {
        $this->division->loadMissing('leader')->loadCount('users');
        $this->syncDerivedData();
    }

    protected function syncDerivedData(): void
    {
        $this->leaderName = optional($this->division->leader)->name;
        $this->memberCount = (int) ($this->division->users_count ?? 0);
    }

    public function toggleMenu(): void
    {
        $this->menuOpen = !$this->menuOpen;
        if (!$this->menuOpen) {
            $this->confirmingDelete = false;
        }
    }

    public function closeMenu(): void
    {
        $this->menuOpen = false;
        $this->confirmingDelete = false;
    }

    public function confirmDelete(): void
    {
        $this->confirmingDelete = true;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDelete = false;
    }

    public function deleteDivision(DivisionService $divisionService): void
    {
        $divisionService->deleteDivision($this->division->id);
        $this->dispatch('division-deleted');
        $this->closeMenu();
    }

    public function render()
    {
        return view('livewire.admin.divisions.row');
    }
}
