<?php

namespace App\Livewire\Admin\Divisions;

use App\Services\Admin\AdminDivisionService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class Index extends Component
{
    use WithPagination;

    public $queryString = [
        'search' => ['except' => ''],
    ];

    public string $search = '';
    public bool $showCreateDivisionModal = false;

    public function mount(): void
    {
        if (request()->routeIs('admin.divisions.create')) {
            $this->showCreateDivisionModal = true;
        }
    }

    public function getDivisionsProperty(AdminDivisionService $adminDivisionService): LengthAwarePaginator
    {
        $validated = $this->validate();

        return $adminDivisionService
            ->paginateForIndex(filters: $validated)
            ->withQueryString();
    }

    protected function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
        ];
    }

    #[On('division-created')]
    public function refreshList(): void
    {
        $this->resetPage();
        $this->showCreateDivisionModal = false;
    }

    #[On('division-deleted')]
    public function handleDivisionDeleted(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->search = trim($this->search);
        $this->resetPage();
    }

    public function openCreateDivisionModal(): void
    {
        $this->showCreateDivisionModal = true;
    }

    public function closeCreateDivisionModal(): void
    {
        $this->showCreateDivisionModal = false;
    }

    public function render()
    {
        return view('livewire.admin.divisions.index');
    }
}
