<?php

namespace App\Livewire;

use Carbon\Carbon;
use Livewire\Component;

class TimeSelector extends Component
{
    /** Custom date/time chosen by tester (format: Y-m-d\TH:i for datetime-local input) */
    public ?string $customDateTime = null;

    /** Currently active test time in human readable format (null when real time) */
    public ?string $currentTestTime = null;

    /** Quick preset shortcuts for common test scenarios */
    public array $quickPresets = [
        '2026-01-01 10:00' => '1 Jan 2026 - KPI Creation',
        '2026-01-21 10:00' => '21 Jan 2026 - Monthly Eval',
        '2026-06-26 10:00' => '26 Jun 2026 - Semester Appraisal',
    ];

    public function mount(): void
    {
        $stored = session('test_time_value');

        if ($stored) {
            $carbon = Carbon::parse($stored);
            $this->customDateTime = $carbon->format('Y-m-d\TH:i');
            $this->currentTestTime = $carbon->format('d M Y H:i');
        } else {
            // Default the picker to current real time so user has a starting point
            $this->customDateTime = Carbon::now()->format('Y-m-d\TH:i');
        }
    }

    /**
     * Apply the chosen custom date/time as the test time.
     */
    public function apply(): void
    {
        $this->validate([
            'customDateTime' => ['required', 'date'],
        ]);

        $testTime = Carbon::parse($this->customDateTime);

        session([
            'test_time_value' => $testTime->toDateTimeString(),
        ]);

        $this->currentTestTime = $testTime->format('d M Y H:i');
        $this->dispatch('time-changed');
    }

    /**
     * Apply one of the predefined quick presets.
     */
    public function applyPreset(string $preset): void
    {
        $testTime = Carbon::parse($preset);

        $this->customDateTime = $testTime->format('Y-m-d\TH:i');

        session([
            'test_time_value' => $testTime->toDateTimeString(),
        ]);

        $this->currentTestTime = $testTime->format('d M Y H:i');
        $this->dispatch('time-changed');
    }

    /**
     * Clear test time and return to real-time mode.
     */
    public function resetToRealTime(): void
    {
        session()->forget('test_time_value');
        // legacy key cleanup, harmless if absent
        session()->forget('test_time_key');

        $this->currentTestTime = null;
        $this->customDateTime = Carbon::now()->format('Y-m-d\TH:i');
        $this->dispatch('time-changed');
    }

    public function render()
    {
        return view('livewire.time-selector');
    }
}
