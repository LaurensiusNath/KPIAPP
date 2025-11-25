<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Facades\Crypt;
use Livewire\Attributes\On;
use Livewire\Component;

class UsersUpdate extends Component
{
    public ?int $userId = null;
    public bool $showModal = false;

    // Form fields
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = '';

    // Listen untuk event editUser dari row
    #[On('editUser')]
    public function openModal(int $userId, UserService $userService)
    {
        $this->userId = $userId;
        $this->loadUserData($userService);
        $this->showModal = true;
    }

    protected function rules(): array
    {
        return  [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email,' . $this->userId,
            'password' => 'required|min:6',
            'role' => 'required|in:admin,team-leader,user',
        ];
    }

    // Load data user untuk edit
    public function loadUserData(UserService $userService)
    {
        if (!$this->userId) {
            return;
        }

        // Get user from service
        $user = $userService->findUserById($this->userId);

        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;

        // Decrypt password
        try {
            $this->password = Crypt::decryptString($user->password);
        } catch (\Exception $e) {
            // If decryption fails (e.g., bcrypt hash), leave password empty
            // User must enter a new password to update
            $this->password = '';
        }
    }

    // Update user
    public function updateUser(UserService $userService)
    {
        // Validasi
        $validated = $this->validate();

        // Prepare update data
        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        // Only update password if provided
        if (!empty($validated['password'])) {
            $updateData['password'] = $validated['password'];
        }

        // Update user via UserService
        $userService->updateUser($this->userId, $updateData);

        // Dispatch event untuk refresh table SEBELUM close modal
        $this->dispatch('user-updated');

        // Flash message
        session()->flash('message', 'User updated successfully!');

        // Close modal & reset - dilakukan terakhir
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
        return view('livewire.admin.users-update');
    }
}
