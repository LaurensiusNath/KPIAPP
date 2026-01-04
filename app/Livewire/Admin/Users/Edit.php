<?php

namespace App\Livewire\Admin\Users;

use App\Services\UserService;
use Illuminate\Support\Facades\Crypt;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public ?int $userId = null;
    public bool $showModal = false;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = '';

    #[On('editUser')]
    public function openModal(int $userId, UserService $userService)
    {
        $this->userId = $userId;
        $this->loadUserData($userService);
        $this->showModal = true;
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email,' . $this->userId,
            'password' => 'required|min:6',
            'role' => 'required|in:admin,team-leader,user',
        ];
    }

    public function loadUserData(UserService $userService)
    {
        if (!$this->userId) {
            return;
        }

        $user = $userService->findUserById($this->userId);

        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;

        try {
            $this->password = Crypt::decryptString($user->password);
        } catch (\Exception $e) {
            $this->password = '';
        }
    }

    public function updateUser(UserService $userService)
    {
        $validated = $this->validate();

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = $validated['password'];
        }

        $userService->updateUser($this->userId, $updateData);

        $this->dispatch('user-updated');

        session()->flash('message', 'User updated successfully!');

        $this->closeModal();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['userId', 'name', 'email', 'password', 'role']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.users.edit');
    }
}
