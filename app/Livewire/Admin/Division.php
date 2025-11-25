<?php

namespace App\Livewire\Admin;

use App\Models\Division as DivisionModel;
use App\Models\User;
use App\Services\DivisionService;
use App\Services\UserService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class Division extends Component
{
    use WithPagination;

    public DivisionModel $divisionData;

    // Modal states
    public bool $showChangeLeaderModal = false;
    public bool $showAddUserModal = false;
    public ?int $selectedNewLeaderId = null;
    public array $selectedUsers = [];

    public function mount(DivisionModel $division): void
    {
        $this->divisionData = $division->load('leader');
    }

    public function getLeaderProperty(UserService $userService): ?User
    {
        return $userService->getLeaderByDivision($this->divisionData->id);
    }

    public function getStaffProperty(UserService $userService): LengthAwarePaginator
    {
        return $userService->getUsersByDivision($this->divisionData->id);
    }

    public function getAvailableLeadersProperty(UserService $userService): Collection
    {
        return $userService->getAvailableLeaders($this->divisionData->id);
    }

    public function getAvailableUsersProperty(UserService $userService): Collection
    {
        return $userService->getAvailableUsers();
    }

    public function openChangeLeaderModal(): void
    {
        $this->showChangeLeaderModal = true;
        $this->selectedNewLeaderId = null;
    }

    public function openAddUserModal(): void
    {
        $this->showAddUserModal = true;
        $this->selectedUsers = [];
    }

    public function changeLeader(DivisionService $divisionService): void
    {
        $this->validate([
            'selectedNewLeaderId' => 'required|exists:users,id',
        ]);

        $divisionService->changeLeader($this->divisionData->id, $this->selectedNewLeaderId);

        // Refresh division data
        $this->divisionData->refresh();

        $this->showChangeLeaderModal = false;
        $this->selectedNewLeaderId = null;
        $this->resetPage();
    }

    public function addUserToDivision(UserService $userService): void
    {
        $this->validate([
            'selectedUsers' => 'required|array|min:1',
            'selectedUsers.*' => 'exists:users,id',
        ], [
            'selectedUsers.required' => 'Please select at least one user to add.',
            'selectedUsers.min' => 'Please select at least one user to add.',
        ]);

        foreach ($this->selectedUsers as $userId) {
            $userService->assignUserToDivision($userId, $this->divisionData->id);
        }

        // Reset state
        $this->selectedUsers = [];
        $this->showAddUserModal = false;

        // Refresh pagination
        $this->resetPage();
    }

    public function removeUserFromDivision(int $userId, UserService $userService): void
    {
        $userService->removeUserFromDivision($userId);
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.admin.division');
    }
}
