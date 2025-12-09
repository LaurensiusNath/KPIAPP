<?php

namespace App\Livewire\Admin;

use App\Models\Division;
use App\Services\DivisionService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class Divisions extends Component
{
    use WithPagination;

    public $queryString = [
        'search' => ['except' => ''],
    ];

    public string $search = '';
    public bool $showCreateDivisionModal = false;

    public function getDivisionsProperty()
    {
        $validated = $this->validate();
        $query = Division::query()
            ->with('leader') // Eager load to prevent N+1
            ->withCount('users') // Count staff (excluding leader)
            ->orderBy('name', 'asc');

        $term = trim($validated['search']);
        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'ilike', '%' . $term . '%')
                    ->orWhereHas('leader', function ($q2) use ($term) {
                        $q2->where('name', 'ilike', '%' . $term . '%');
                    });
            });
        }

        return $query->paginate(20)->withQueryString();
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

    public function deleteDivision(int $divisionId, DivisionService $divisionService): void
    {
        $divisionService->deleteDivision($divisionId);
        // Reset to first page if current page becomes invalid after deletion
        $this->resetPage();
    }
    public function render()
    {
        return view('livewire.admin.divisions');
    }
}
