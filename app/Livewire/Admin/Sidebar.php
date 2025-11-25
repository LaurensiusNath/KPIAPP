<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Sidebar extends Component
{
    public function logout()
    {
        Auth::logout();

        return redirect()->route('init');
    }
    public function render()
    {
        return view('livewire.admin.sidebar');
    }
}
