<?php

namespace App\Livewire\Admin\Divisions;

use App\Rules\AvailableLeader;
use App\Services\DivisionService;
use App\Services\UserService;
use Livewire\Component;

class Create extends Component
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
                new AvailableLeader(),
            ],
        ];
    }

    public function render()
    {
        return view('livewire.admin.divisions.create');
    }
}
