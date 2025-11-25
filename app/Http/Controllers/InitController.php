<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InitController extends Controller
{
    public function initialize(Request $request)
    {
        if ($request->user()) {
            switch ($request->user()->role) {
                case 'admin':
                    return redirect()->to('/admin/dashboard');
                    break;
                case 'team-leader':
                    return redirect()->to('/team-leader/dashboard');
                    break;
                default:
                    return redirect()->route('dashboard');
            }
        } else {
            return redirect()->route('login');
        }
    }
}
