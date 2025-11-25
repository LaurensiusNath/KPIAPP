<?php

namespace App\Livewire\TeamLeader;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Sidebar extends Component
{
    public function logout(): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        redirect()->route('init');
    }

    public function render()
    {
        return view('livewire.team-leader.sidebar');
    }
}
