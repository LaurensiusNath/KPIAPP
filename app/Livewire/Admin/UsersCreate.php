<?php

namespace App\Livewire\Admin;

use App\Services\UserService;
use Livewire\Component;

class UsersCreate extends Component
{
    public string $name;
    public string $email;
    public string $password;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:4', 'max:255'],
            'email' => ['required', 'email:dns', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    public function createUser(UserService $userService)
    {
        $validated = $this->validate();

        $userService->createUser([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'user',
        ]);

        // Dispatch event to refresh parent list
        $this->dispatch('user-created');

        // Reset form fields
        $this->reset(['name', 'email', 'password']);
    }
    public function render()
    {
        return view('livewire.admin.users-create');
    }
}
