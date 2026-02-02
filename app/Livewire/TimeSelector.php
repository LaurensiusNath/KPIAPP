<?php

namespace App\Livewire;

use Carbon\Carbon;
use Livewire\Component;

class TimeSelector extends Component
{
    public $selectedTime = 'real';
    public $currentTestTime = null;

    public $availableTimes = [
        'real' => 'Real Time',
        '2026-01-01' => '1 Januari 2026 (KPI Creation)',
        '2026-01-21' => '21 Januari 2026 (Monthly Eval)',
        '2026-06-26' => '26 Juni 2026 (Semester Appraisal)',
    ];

    public function mount()
    {
        // Load dari session jika ada
        $this->selectedTime = session('test_time_key', 'real');
        $this->updateCurrentTestTime();
    }

    public function updatedSelectedTime()
    {
        if ($this->selectedTime === 'real') {
            // Reset ke waktu sebenarnya
            session()->forget('test_time_key');
            session()->forget('test_time_value');
            $this->currentTestTime = null;
        } else {
            // Set waktu testing - only store in session, no Carbon manipulation
            $testTime = Carbon::parse($this->selectedTime . ' 10:00:00');
            session(['test_time_key' => $this->selectedTime]);
            session(['test_time_value' => $testTime->toDateTimeString()]);
            $this->currentTestTime = $testTime->format('d M Y H:i');
        }

        // Just refresh the component, don't reload page
        $this->dispatch('time-changed');
    }

    private function updateCurrentTestTime()
    {
        $testTimeValue = session('test_time_value');
        if ($testTimeValue) {
            $this->currentTestTime = Carbon::parse($testTimeValue)->format('d M Y H:i');
        } else {
            $this->currentTestTime = null;
        }
    }

    public function render()
    {
        return view('livewire.time-selector');
    }
}
