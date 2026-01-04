<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use App\Services\Admin\AdminUserService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class Index extends Component
{
    use WithPagination;

    public $queryString = [
        'sort_by' => ['except' => 'name_asc'],
        'search' => ['except' => ''],
        'divisionFilter' => ['as' => 'division', 'except' => null],
    ];

    public string $sort_by = 'name_asc';
    public string $search = '';
    public ?int $divisionFilter = null;
    public bool $showCreateUserModal = false;

    public function mount(?User $user = null): void
    {
        if (request()->routeIs('admin.users.create')) {
            $this->showCreateUserModal = true;
        }

        if (request()->routeIs('admin.users.edit') && $user) {
            $this->dispatch('editUser', userId: $user->id);
        }
    }

    public function getUsersProperty(AdminUserService $adminUserService): LengthAwarePaginator
    {
        $validated = $this->validate();

        return $adminUserService
            ->paginateForIndex(
                filters: $validated,
                excludeUserId: (int) Auth::id(),
            )
            ->withQueryString();
    }

    public function getDivisionsProperty(AdminUserService $adminUserService)
    {
        return $adminUserService->getDivisionsForFilter();
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
        return view('livewire.admin.users.index');
    }
}
