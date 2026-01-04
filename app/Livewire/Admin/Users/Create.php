<?php

namespace App\Livewire\Admin\Users;

use App\Services\UserService;
use Livewire\Component;

class Create extends Component
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

        $this->dispatch('user-created');

        $this->reset(['name', 'email', 'password']);
    }

    public function render()
    {
        return view('livewire.admin.users.create');
    }
}
