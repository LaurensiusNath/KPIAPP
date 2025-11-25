<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\Division;
use App\Services\DivisionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class Users extends Component
{

    use WithPagination;

    public $queryString = [
        'sort_by' => ['except' => 'name_asc'],
        'search' => ['except' => ''],
        'divisionFilter' => ['as' => 'division', 'except' => null],
    ];

    public string $sort_by = "name_asc";
    public string $search = '';
    public ?int $divisionFilter = null;
    public bool $showCreateUserModal = false;

    public function getUsersProperty()
    {
        $validated = $this->validate();
        $usersQuery = User::query()
            ->with('division')
            ->whereIn('role', ['team-leader', 'user'])
            ->where('id', '!=', Auth::id());

        switch ($validated['sort_by']) {
            case 'name_asc':
                $usersQuery->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $usersQuery->orderBy('name', 'desc');
                break;
            default:
                $usersQuery->latest();
        }

        $term = trim($validated['search']);
        if ($term !== '') {
            $usersQuery->where(function ($query) use ($term) {
                $query->where('name', 'ilike', '%' . $term . '%')
                    ->orWhere('email', 'ilike', '%' . $term . '%');
            });
        }

        if (!empty($this->divisionFilter)) {
            $usersQuery->where('division_id', $this->divisionFilter);
        }

        return $usersQuery->paginate(25)->withQueryString();
    }

    public function getDivisionsProperty(DivisionService $divisionService)
    {
        return $divisionService->getAllDivisions();
    }

    public function updatedSearch(): void
    {
        $this->search = trim($this->search);
        $this->resetPage();
    }

    public function updatedSortBy(): void
    {
        $this->resetPage();
    }

    public function updatedDivisionFilter(): void
    {
        $this->resetPage();
    }

    #[On(['user-updated', 'user-created'])]
    public function refreshList(): void
    {
        $this->resetPage();
        // Close the create modal on successful create/update events (child emits 'user-created')
        $this->showCreateUserModal = false;
    }

    protected function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'sort_by' => 'nullable|in:name_asc,name_desc',
            'divisionFilter' => 'nullable|integer|exists:divisions,id',
        ];
    }

    public function openCreateUserModal(): void
    {
        $this->showCreateUserModal = true;
    }

    public function closeCreateUserModal(): void
    {
        $this->showCreateUserModal = false;
    }

    public function render()
    {
        return view('livewire.admin.users');
    }
}
