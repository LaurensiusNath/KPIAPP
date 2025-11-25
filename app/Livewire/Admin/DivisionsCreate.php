<?php

namespace App\Livewire\Admin;

use App\Services\DivisionService;
use App\Services\UserService;
use Illuminate\Validation\Rule;
use Livewire\Component;

class DivisionsCreate extends Component
{
    public string $name;
    public int $leaderId;

    public function createDivision(DivisionService $divisionService, UserService $userService)
    {
        $validated = $this->validate();

        $divisionService->createDivision([
            'name' => $validated['name'],
            'leader_id' => $validated['leaderId'],
        ]);

        $this->dispatch('division-created');
        $this->reset(['name', 'leaderId']);
    }

    public function getUsersProperty(UserService $userService)
    {
        return $userService->getAvailableLeaders();
    }

    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'leaderId' => [
                'required',
                'integer',
                // Hanya boleh memilih user yang role-nya 'user' dan belum memiliki division (division_id null)
                Rule::exists('users', 'id')->where(
                    fn($query) => $query
                        ->where('role', 'user')
                        ->whereNull('division_id')
                ),
            ],
        ];
    }
    public function render()
    {
        return view('livewire.admin.divisions-create');
    }
}
